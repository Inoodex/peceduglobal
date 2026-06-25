<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

/**
 * Private channel per authenticated user.
 * Used by NotificationCreated event to deliver real-time notifications.
 * The authenticated user can only subscribe to their own channel.
 */
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

/**
 * Admin chat channel — already used by the chat system.
 */
Broadcast::channel('admin-chat', function ($user) {
    return in_array($user->role, ['admin', 'consultant']);
});

/**
 * Per-conversation chat channel.
 */
Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    return in_array($user->role, ['admin', 'consultant']);
});
