<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List notifications for the authenticated admin.
     *
     * Query params:
     *  - unread_only=1   → only unread rows (used for badge count)
     */
    public function index(Request $request)
    {
        $userId = $request->user()?->id;

        $query = AppNotification::forUser($userId)->latest();

        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $notifications = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $notifications,
        ]);
    }

    /**
     * Unread count for the authenticated admin (used by the bell badge).
     */
    public function unreadCount(Request $request)
    {
        $userId = $request->user()?->id;

        $count = AppNotification::forUser($userId)->unread()->count();

        return response()->json([
            'success' => true,
            'data'    => ['unread_count' => $count],
        ]);
    }

    /**
     * Create a new notification.
     *
     * Typically called by the admin frontend itself — the chat store decides
     * whether the admin is currently viewing a conversation, and only requests
     * a notification be persisted when the message is genuinely unseen.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'           => 'nullable|string',
            'title'          => 'required|string',
            'body'           => 'nullable|string',
            'data'           => 'nullable|array',
            'action_url'     => 'nullable|string',
            'action_params'  => 'nullable|array',
        ]);

        // De-duplicate: avoid stacking many notifications for the same chat
        // message within a short window. Match on message id in `data`.
        $messageId = $data['data']['message']['id'] ?? null;
        if ($messageId) {
            $existing = AppNotification::where('user_id', $request->user()?->id)
                ->where('type', $data['type'] ?? 'chat_message')
                ->where('data->message->id', $messageId)
                ->latest()
                ->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'data'    => $existing,
                ]);
            }
        }

        $data['user_id'] = $request->user()?->id;
        $notification = AppNotification::create($data);

        return response()->json([
            'success' => true,
            'data'    => $notification,
        ], 201);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead($id)
    {
        $notification = AppNotification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data'    => $notification->fresh(),
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated admin.
     */
    public function markAllRead(Request $request)
    {
        $userId = $request->user()?->id;

        AppNotification::forUser($userId)
            ->unread()
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * Mark all chat_message notifications tied to a specific conversation as read.
     *
     * Called when the admin opens that conversation — Messenger-style: once you
     * open the chat, the bell badge clears for it. Matches on the message's
     * conversation_id stored inside the notification's `data` JSON column.
     */
    public function markConversationRead(Request $request, $conversationId)
    {
        $userId = $request->user()?->id;

        $updated = AppNotification::forUser($userId)
            ->unread()
            ->where('type', 'chat_message')
            ->where('data->message->conversation_id', (int) $conversationId)
            ->update(['is_read' => true]);

        $count = AppNotification::forUser($userId)->unread()->count();

        return response()->json([
            'success'     => true,
            'data'        => [
                'marked_read'  => $updated,
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * Delete a single notification.
     */
    public function destroy($id)
    {
        $notification = AppNotification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted.',
        ]);
    }
}
