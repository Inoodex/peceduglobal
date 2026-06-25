<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification for a specific user.
     *
     * @param  int         $userId     Target user ID
     * @param  string      $type       Notification type slug (e.g. 'appointment_booked')
     * @param  string      $title      Short title shown in the bell panel
     * @param  string      $body       Longer body text
     * @param  string|null $actionUrl  Frontend route the user should go to on click
     * @param  array       $actionParams  Extra params (e.g. ['appointmentId' => 5])
     * @param  array       $data       Free-form JSON data to store alongside the notification
     */
    public static function forUser(
        int|string $userId,
        string $type,
        string $title,
        string $body = '',
        ?string $actionUrl = null,
        array $actionParams = [],
        array $data = []
    ): AppNotification {
        $notification = AppNotification::create([
            'user_id'       => $userId,
            'type'          => $type,
            'title'         => $title,
            'body'          => $body,
            'action_url'    => $actionUrl,
            'action_params' => $actionParams ?: null,
            'data'          => $data ?: null,
            'is_read'       => false,
        ]);

        // Broadcast real-time via DynamicPusherService (same as chat system — uses DB credentials)
        try {
            /** @var DynamicPusherService $pusher */
            $pusher = app(DynamicPusherService::class);
            $pusher->trigger(
                "notification-user-{$userId}",
                'notification.created',
                ['notification' => $notification->toArray()]
            );
        } catch (\Throwable $e) {
            // Best effort — never let a broadcast failure break the main request
        }

        return $notification;
    }

    /**
     * Broadcast a notification to every admin (role = 'admin').
     * A single row with user_id = NULL is NOT used here; instead one row
     * per admin is created so that each admin can independently mark it read.
     */
    public static function toAllAdmins(
        string $type,
        string $title,
        string $body = '',
        ?string $actionUrl = null,
        array $actionParams = [],
        array $data = []
    ): void {
        $adminIds = User::where('role', 'admin')->pluck('id');

        foreach ($adminIds as $adminId) {
            self::forUser($adminId, $type, $title, $body, $actionUrl, $actionParams, $data);
        }
    }

    /**
     * Broadcast a notification to every admin AND every consultant.
     */
    public static function toAllAdminsAndConsultants(
        string $type,
        string $title,
        string $body = '',
        ?string $actionUrl = null,
        array $actionParams = [],
        array $data = []
    ): void {
        $userIds = User::whereIn('role', ['admin', 'consultant'])->pluck('id');

        foreach ($userIds as $userId) {
            self::forUser($userId, $type, $title, $body, $actionUrl, $actionParams, $data);
        }
    }
}
