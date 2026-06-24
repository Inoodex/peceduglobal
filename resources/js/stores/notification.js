import { defineStore } from 'pinia';
import axios from '@/plugins/axios';

/**
 * Notification store.
 *
 * IMPORTANT — frontend-driven design:
 * The backend does NOT decide whether to create a notification (it has no way
 * to know which conversation the admin is currently viewing). Instead, the
 * chat store calls `handleIncomingMessage()` here only when the admin is NOT
 * viewing that conversation. This store then:
 *   1. Creates a notification row via API (so it persists across reloads/tabs)
 *   2. Updates the local unread count + list
 *   3. Plays a notification sound
 */
export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: [],
        unreadCount: 0,
        isOpen: false,
        activeTab: 'messages',
        loading: false,
        _sound: null,
        _audioCtx: null,
    }),

    getters: {
        unreadTotal: (state) => state.unreadCount,

        messageNotifications: (state) =>
            state.notifications.filter((n) => (n.type || '') === 'chat_message'),

        otherNotifications: (state) =>
            state.notifications.filter((n) => (n.type || '') !== 'chat_message'),

        visibleNotifications() {
            return this.activeTab === 'messages'
                ? this.messageNotifications
                : this.notifications;
        },
    },

    actions: {
        /* ------------------------------ API actions ------------------------------ */

        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await axios.get('/auth/admin/notifications');
                if (response.data?.success) {
                    // API returns a paginator — take the items.
                    const data = response.data.data;
                    this.notifications = Array.isArray(data)
                        ? data
                        : data.data || [];
                }
            } catch (e) {
                console.error('🚨 [notificationStore] fetch failed', e);
            } finally {
                this.loading = false;
            }
        },

        async fetchUnreadCount() {
            try {
                const response = await axios.get('/auth/admin/notifications/unread-count');
                if (response.data?.success) {
                    this.unreadCount = Number(response.data.data?.unread_count || 0);
                }
            } catch (e) {
                console.error('🚨 [notificationStore] unread-count failed', e);
            }
        },

        async markRead(id) {
            try {
                await axios.post(`/auth/admin/notifications/${id}/read`);
                const n = this.notifications.find((x) => x.id === id);
                if (n && !n.is_read) {
                    n.is_read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
            } catch (e) {
                console.error('🚨 [notificationStore] markRead failed', e);
            }
        },

        async markAllRead() {
            try {
                await axios.post('/auth/admin/notifications/read-all');
                this.notifications.forEach((n) => (n.is_read = true));
                this.unreadCount = 0;
            } catch (e) {
                console.error('🚨 [notificationStore] markAllRead failed', e);
            }
        },

        /**
         * Mark all chat_message notifications tied to a specific conversation as read.
         * Called when the admin opens that conversation — Messenger-style: once you
         * open the chat, the bell badge clears for it. Hits a dedicated server
         * endpoint so the DB rows actually flip to read (otherwise a later
         * fetchUnreadCount() would just bring them back as unread).
         */
        async clearChatNotificationsForConversation(conversationId) {
            const cid = Number(conversationId);
            if (!cid) return;

            // 1) Optimistic local update — flip matching rows immediately so the
            //    bell badge + panel feel instant, matching on the conversationId
            //    stored inside action_params at creation time.
            let cleared = 0;
            this.notifications.forEach((n) => {
                const nCid =
                    Number(n.action_params?.conversationId) ||
                    Number(n.data?.message?.conversation_id);
                if (n.type === 'chat_message' && nCid === cid && !n.is_read) {
                    n.is_read = true;
                    cleared += 1;
                }
            });
            if (cleared > 0) {
                this.unreadCount = Math.max(0, this.unreadCount - cleared);
            }

            // 2) Persist server-side so the DB stays in sync (authoritative).
            try {
                const response = await axios.post(
                    `/auth/admin/notifications/conversations/${cid}/read`
                );
                if (response.data?.success) {
                    // Trust the server's true remaining unread count.
                    this.unreadCount = Number(response.data.data?.unread_count || 0);
                }
            } catch (_) { /* best effort */ }
        },

        async dismiss(id) {
            try {
                await axios.delete(`/auth/admin/notifications/${id}`);
                const idx = this.notifications.findIndex((x) => x.id === id);
                if (idx !== -1) {
                    const removed = this.notifications[idx];
                    this.notifications.splice(idx, 1);
                    if (removed && !removed.is_read) {
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                }
            } catch (e) {
                console.error('🚨 [notificationStore] dismiss failed', e);
            }
        },

        /* ----------------------- Called by the chat store ----------------------- */

        /**
         * Called by chatStore.handleGlobalMessage ONLY when the admin is NOT
         * viewing the incoming message. Creates a persistent notification.
         *
         * @param {object} param0
         * @param {object} param0.message  The chat message payload
         * @param {object} param0.conversation  The conversation object (for guest email)
         */
        async handleIncomingMessage({ message, conversation }) {
            // 1) Optimistic local notification (so the panel updates instantly)
            const tempNotification = {
                id: `local_${message.id}_${Date.now()}`,
                type: 'chat_message',
                title: `New message from ${conversation?.guest?.email || 'a guest'}`,
                body: (message.message || '').slice(0, 100),
                data: { message },
                action_url: '/dashboard/chat',
                action_params: { conversationId: conversation?.id },
                is_read: false,
                created_at: new Date().toISOString(),
                _local: true,
            };
            this.notifications.unshift(tempNotification);
            this.unreadCount += 1;

            // 2) Play sound
            this.playSound();

            // 3) Browser desktop notification (only when the tab is hidden)
            this.notifyBrowser({
                title: tempNotification.title,
                body: tempNotification.body,
            });

            // 4) Persist server-side so other tabs / reloads stay consistent.
            try {
                const response = await axios.post('/auth/admin/notifications', {
                    type: 'chat_message',
                    title: tempNotification.title,
                    body: tempNotification.body,
                    data: { message },
                    action_url: '/dashboard/chat',
                    action_params: { conversationId: conversation?.id },
                });
                if (response.data?.success) {
                    // Replace the optimistic entry with the real row.
                    const idx = this.notifications.findIndex(
                        (n) => n.id === tempNotification.id
                    );
                    if (idx !== -1) {
                        this.notifications[idx] = response.data.data;
                    }
                }
            } catch (e) {
                console.error('🚨 [notificationStore] persist failed', e);
                // Keep the optimistic entry even if persist fails.
            }
        },

        /* --------------------------------- Sound -------------------------------- */

        playSound() {
            // 1) Try the user-supplied mp3 asset first (if present in /public/sounds).
            try {
                if (!this._sound) {
                    this._sound = new Audio('/sounds/notification.mp3');
                }
                this._sound.currentTime = 0;
                const p = this._sound.play();
                if (p && typeof p.then === 'function') {
                    // If the asset is missing, the promise rejects → fall back to WebAudio.
                    p.catch(() => this._playWebAudioTone());
                    return;
                }
                return;
            } catch (_) {
                /* fall through to WebAudio */
            }
            this._playWebAudioTone();
        },

        /**
         * Fallback "ding" synthesized with the Web Audio API.
         * No external asset needed — works even if /sounds/notification.mp3
         * is absent or blocked. Lazy-initializes a shared AudioContext.
         */
        _playWebAudioTone() {
            try {
                const AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return;
                if (!this._audioCtx) this._audioCtx = new AC();
                const ctx = this._audioCtx;
                if (ctx.state === 'suspended') ctx.resume();

                const now = ctx.currentTime;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                // Two-step chime (880Hz → 1320Hz) with a soft envelope.
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, now);
                osc.frequency.setValueAtTime(1320, now + 0.12);

                gain.gain.setValueAtTime(0.0001, now);
                gain.gain.exponentialRampToValueAtTime(0.25, now + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.4);

                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.42);
            } catch (_) {
                /* Audio not available */
            }
        },

        /**
         * Optional browser-level desktop notification (when tab is hidden).
         * Respects the user's permission setting.
         */
        notifyBrowser({ title, body }) {
            try {
                if (typeof Notification === 'undefined') return;
                if (document && !document.hidden) return; // only when tab not focused
                if (Notification.permission === 'granted') {
                    new Notification(title, { body, icon: '/favicon.ico' });
                }
            } catch (_) { /* ignore */ }
        },

        /* ------------------------------- UI helpers ------------------------------ */

        togglePanel() {
            this.isOpen = !this.isOpen;
        },

        closePanel() {
            this.isOpen = false;
        },

        openPanel() {
            this.isOpen = true;
        },
    },
});
