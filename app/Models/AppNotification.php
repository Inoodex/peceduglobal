<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Application-level notification.
 *
 * Table is `app_notifications` to avoid clashing with Laravel's built-in
 * database notification system.
 */
class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $guarded = [];

    protected $casts = [
        'data'          => 'array',
        'action_params' => 'array',
        'is_read'       => 'boolean',
    ];

    /**
     * Scope to only unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to notifications targeted at a specific user (or broadcast to all).
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', $userId);
        });
    }
}
