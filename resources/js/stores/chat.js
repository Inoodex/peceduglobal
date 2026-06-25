import { defineStore } from 'pinia';
import axios from '@/plugins/axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Central chat store — owns the Echo connection and the global conversation list,
 * so the sidebar/badge stay live on EVERY dashboard page (not just the Chat page).
 */
export const useChatStore = defineStore('chat', {
    state: () => ({
        conversations: [],
        activeConversationId: null,
        activeMessages: [],
        echo: null,
        isReady: false,
        // On-page-only UI flags kept here so the Chat view can reuse them
        loadingConversations: false,
        loadingHistory: false,
        sendingReply: false,
        closingChat: false,
    }),

    getters: {
        // Total unread guest messages across all conversations (for the header bell)
        unreadTotal(state) {
            return state.conversations.reduce(
                (sum, c) => sum + (Number(c.unread_count) || 0),
                0
            );
        },

        activeConversation(state) {
            return (
                state.conversations.find(c => c.id === state.activeConversationId) || null
            );
        },
    },

    actions: {
        /* ----------------------------- Echo lifecycle ----------------------------- */

        async initEcho() {
            if (this.echo || this.isReady) return;

            try {
                const response = await axios.get('/public/chat/settings');
                if (!response.data || !response.data.success) {
                    console.error('🚨 [chatStore] Settings API failed', response.data);
                    return;
                }

                const settings = response.data.data;
                console.log('🔔 [chatStore] settings', settings);

                if (!settings.pusher_key) {
                    console.error('🚨 [chatStore] pusher_key missing in DB settings!');
                    return;
                }

                window.Pusher = Pusher;
                const echoOptions = {
                    broadcaster: 'pusher',
                    key: settings.pusher_key,
                    forceTLS: (settings.pusher_scheme || 'https') === 'https',
                    disableStats: true,
                    enabledTransports: ['ws', 'wss'],
                };

                if (settings.pusher_driver === 'custom') {
                    echoOptions.wsHost = settings.pusher_host;
                    echoOptions.wsPort = parseInt(settings.pusher_port) || 6001;
                    echoOptions.wssPort = parseInt(settings.pusher_port) || 6001;
                } else {
                    echoOptions.cluster = settings.pusher_cluster;
                }

                this.echo = new Echo(echoOptions);

                const pusherInstance = this.echo.connector.pusher;
                pusherInstance.connection.bind('state_change', (states) => {
                    console.log('🔔 [PUSHER STATE]', states.previous, '➜', states.current);
                });
                pusherInstance.connection.bind('connected', () => {
                    console.log('🔔 ✅ [PUSHER CONNECTED] socket_id:', pusherInstance.connection.socket_id);
                });
                pusherInstance.connection.bind('error', (err) => {
                    console.error('🚨 [PUSHER ERROR]', err);
                });

                this.isReady = true;
            } catch (e) {
                console.error('🚨 [chatStore] initEcho failed', e);
            }
        },

        /**
         * Subscribe to the GLOBAL admin channel. Call this once from MainLayout
         * so the sidebar/badge update on every dashboard page.
         */
        async subscribeGlobal() {
            if (!this.echo) await this.initEcho();
            if (!this.echo) return;

            const channel = this.echo.channel('admin-chat');
            console.log('🔔 [chatStore] subscribed to global admin-chat channel');

            channel.listen('.message.sent', (raw) => {
                console.log('🎉 [GLOBAL] message.sent', raw);
                this.handleGlobalMessage(raw);
            });

            channel.listen('.conversation.closed', (raw) => {
                console.log('🎉 [GLOBAL] conversation.closed', raw);
                const id = Number(raw?.conversation_id);
                const conv = this.conversations.find(c => Number(c.id) === id);
                if (conv) conv.status = 'closed';
            });
        },

        /* ------------------------------ Data loading ------------------------------ */

        async fetchConversations() {
            this.loadingConversations = true;
            try {
                const response = await axios.get('/auth/admin/chat/conversations');
                if (response.data.success) {
                    this.conversations = response.data.data;
                }
            } catch (e) {
                console.error('🚨 [chatStore] fetchConversations failed', e);
            } finally {
                this.loadingConversations = false;
            }
        },

        async openConversation(id) {
            // Normalize to a number so handleGlobalMessage's `===` comparison is exact
            // (avodes string/number mismatch that would break the "open = read" gate).
            this.activeConversationId = Number(id);
            this.activeMessages = [];
            this.loadingHistory = true;

            try {
                // Optimistically reset unread badge
                const conv = this.conversations.find(c => Number(c.id) === Number(id));
                if (conv) conv.unread_count = 0;

                // Load messages (also marks them read server-side)
                const response = await axios.get(`/auth/admin/chat/conversations/${id}/messages`);
                if (response.data.success) {
                    this.activeMessages = response.data.data;
                }

                // Also hit mark-read to be explicit + refresh unread on server
                try {
                    await axios.post(`/auth/admin/chat/conversations/${id}/mark-read`);
                } catch (_) { /* best effort */ }

                // Sync the notification store: clear any unread chat_message entries
                // for this conversation so the bell badge doesn't stay stale after
                // the admin has clearly opened (and thus read) the conversation.
                try {
                    const { useNotificationStore } = await import('@/stores/notification');
                    const notif = useNotificationStore();
                    notif.clearChatNotificationsForConversation(Number(id));
                } catch (_) { /* best effort */ }
            } catch (e) {
                console.error('🚨 [chatStore] openConversation failed', e);
            } finally {
                this.loadingHistory = false;
            }
        },

        async sendReply(messageText) {
            if (!this.activeConversationId || !messageText?.trim()) return null;
            this.sendingReply = true;

            // Optimistic update — show the message instantly before the API responds.
            // Use a temporary negative id so _appendIfNew won't duplicate it.
            const tempId = -(Date.now());
            const optimisticMsg = {
                id: tempId,
                conversation_id: this.activeConversationId,
                message: messageText,
                sender_type: 'App\\Models\\User',
                is_read: false,
                created_at: new Date().toISOString(),
                sender_name: 'You',
                _optimistic: true,
            };
            this.activeMessages.push(optimisticMsg);

            try {
                const response = await axios.post(
                    `/auth/admin/chat/conversations/${this.activeConversationId}/reply`,
                    { message: messageText }
                );
                if (response.data.success) {
                    const newMsg = response.data.data;
                    // The backend ALSO broadcasts message.sent on the global admin-chat
                    // channel, so handleGlobalMessage may have already appended newMsg
                    // by the time this HTTP response resolves (and vice-versa). The old
                    // splice-in-place assumed the HTTP response always won that race —
                    // when Pusher won, it left both the Pusher-delivered copy AND the
                    // spliced copy, producing duplicate bubbles. Make this order-proof:
                    // drop the optimistic placeholder unconditionally, then append the
                    // confirmed message only if it isn't already present.
                    this.activeMessages = this.activeMessages.filter(m => m.id !== tempId);
                    this._appendIfNew(newMsg);
                    this._bubbleConversationToTop(this.activeConversationId, newMsg);
                    return newMsg;
                }
            } catch (e) {
                console.error('🚨 [chatStore] sendReply failed', e);
                // Remove the optimistic message on failure so the UI is consistent.
                this.activeMessages = this.activeMessages.filter(m => m.id !== tempId);
            } finally {
                this.sendingReply = false;
            }
            return null;
        },

        async closeActiveConversation() {
            if (!this.activeConversationId) return false;
            this.closingChat = true;
            try {
                const response = await axios.post(
                    `/auth/admin/chat/conversations/${this.activeConversationId}/close`
                );
                if (response.data.success) {
                    const conv = this.conversations.find(c => c.id === this.activeConversationId);
                    if (conv) conv.status = 'closed';
                    return true;
                }
            } catch (e) {
                console.error('🚨 [chatStore] closeActiveConversation failed', e);
            } finally {
                this.closingChat = false;
            }
            return false;
        },

        clearActiveConversation() {
            this.activeConversationId = null;
            this.activeMessages = [];
        },

        disconnect() {
            if (this.echo) {
                try { this.echo.leave('admin-chat'); } catch (_) {}
                // Per-conversation channels are managed by the Chat view too; leaving
                // the global channel here is the safe minimum.
                this.echo.disconnect();
                this.echo = null;
                this.isReady = false;
            }
        },

        /* ------------------------------ Internal utils ----------------------------- */

        handleGlobalMessage(raw) {
            const safeParse = (v) => (typeof v === 'string' ? safeJsonParse(v) : v);
            const payload = safeParse(raw) || {};
            const message = safeParse(payload.message) || null;

            const conversationId = Number(payload.conversation_id || message?.conversation_id);
            const messageId = message?.id;

            if (!conversationId || !messageId) {
                console.warn('⚠️ [GLOBAL] skipping — missing ids', raw);
                return;
            }

            const senderType = message.sender_type || '';
            const isAdminOwnReply = senderType.includes('User');
            // Messenger-style: "open" is the single source of truth for read state.
            // If the conversation window is open, the message is read — regardless of
            // whether the browser tab is focused. Tab visibility only controls whether
            // we also fire a sound / desktop notification (not the unread badge).
            const isOpen = Number(this.activeConversationId) === conversationId;
            const isTabVisible = typeof document !== 'undefined' && !document.hidden;

            // 1) If this conversation is currently open, append to the message window
            if (isOpen) {
                this._appendIfNew(message);
                // Admin's own reply was optimistically inserted with a negative temp id.
                // If the Pusher echo beat the HTTP response, that placeholder is still
                // here next to the real message — drop it now so we never render both.
                if (isAdminOwnReply) {
                    this.activeMessages = this.activeMessages.filter(
                        m => !(m._optimistic && m.conversation_id === conversationId)
                    );
                }
            }

            // 2) Refresh last_message + unread for the sidebar list
            const idx = this.conversations.findIndex(c => Number(c.id) === conversationId);
            if (idx !== -1) {
                const conv = this.conversations[idx];
                conv.last_message = message;

                if (isAdminOwnReply) {
                    // Admin's own reply — never touch the unread badge.
                    // unread_count is for GUEST messages only.
                } else if (isOpen) {
                    // Conversation is OPEN → message is read instantly (Messenger style).
                    // Enforce unread = 0 locally so a stale server payload can't override it.
                    conv.unread_count = 0;
                    // Tell the server this message is read so other tabs / reloads stay in sync.
                    this._silentMarkRead(conversationId);

                    // Even though it's "read", if the admin is on ANOTHER tab right now
                    // (conversation open but tab hidden), still play a soft sound + desktop
                    // notification so they don't miss it. No badge, no notification entry.
                    if (!isTabVisible) {
                        try {
                            import('@/stores/notification').then(({ useNotificationStore }) => {
                                const notif = useNotificationStore();
                                notif.playSound();
                                notif.notifyBrowser({
                                    title: `New message from ${conv.guest?.email || 'a guest'}`,
                                    body: (message.message || '').slice(0, 100),
                                });
                            });
                        } catch (_) { /* best effort */ }
                    }
                } else {
                    // Conversation is CLOSED → genuinely unseen. Increment badge.
                    conv.unread_count = Number(payload.unread_count ?? (conv.unread_count + 1));
                    // Create a full notification entry + sound + desktop notification.
                    try {
                        import('@/stores/notification').then(({ useNotificationStore }) => {
                            const notif = useNotificationStore();
                            notif.handleIncomingMessage({ message, conversation: conv });
                        });
                    } catch (_) { /* notification store not available yet */ }
                }

                this._bubbleConversationToTop(conversationId, message);
            } else if (!isAdminOwnReply) {
                // ── NEW CONVERSATION ──────────────────────────────────────────────────
                // This message belongs to a conversation we've never seen before
                // (a brand-new student just initiated a chat). Fetch the full
                // conversation object from the server and prepend it to the sidebar
                // so the admin sees it immediately without a page refresh.
                this._fetchAndPrependConversation(conversationId, message);
            }
        },

        /**
         * Fetch a single conversation by id and prepend it to the sidebar list.
         * Called when a message.sent event arrives for an unknown conversation.
         */
        async _fetchAndPrependConversation(conversationId, triggerMessage) {
            try {
                const response = await axios.get('/auth/admin/chat/conversations');
                if (!response.data.success) return;

                const fresh = (response.data.data || []).find(
                    c => Number(c.id) === conversationId
                );
                if (!fresh) return;

                // Avoid duplicates (race condition: two events for same conversation).
                const alreadyExists = this.conversations.some(
                    c => Number(c.id) === conversationId
                );
                if (alreadyExists) return;

                // Unread = 1 since this is a brand-new unseen conversation.
                fresh.unread_count = fresh.unread_count ?? 1;
                this.conversations.unshift(fresh);

                // Notify admin of the new conversation.
                try {
                    import('@/stores/notification').then(({ useNotificationStore }) => {
                        const notif = useNotificationStore();
                        notif.handleIncomingMessage({ message: triggerMessage, conversation: fresh });
                    });
                } catch (_) { /* best effort */ }
            } catch (e) {
                console.warn('⚠️ [chatStore] _fetchAndPrependConversation failed', e);
            }
        },

        /**
         * Fire-and-forget mark-read call so the server-side unread_count stays in sync
         * while the admin is actively viewing a conversation.
         */
        _silentMarkRead(conversationId) {
            if (!conversationId) return;
            axios
                .post(`/auth/admin/chat/conversations/${conversationId}/mark-read`)
                .catch(() => { /* best effort */ });
        },

        _appendIfNew(message) {
            if (message && message.id && !this.activeMessages.some(m => m && m.id === message.id)) {
                this.activeMessages.push(message);
            }
        },

        _bubbleConversationToTop(conversationId, message) {
            const idx = this.conversations.findIndex(c => Number(c.id) === Number(conversationId));
            if (idx === -1) return;
            const conv = this.conversations[idx];
            conv.last_message = message;
            conv.updated_at = new Date().toISOString();
            this.conversations.splice(idx, 1);
            this.conversations.unshift(conv);
        },
    },
});

function safeJsonParse(str) {
    try { return JSON.parse(str); } catch (e) { return null; }
}
