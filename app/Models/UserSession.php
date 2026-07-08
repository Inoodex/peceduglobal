<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    protected $appends = ['duration_human'];

    protected $fillable = [
        'user_id',
        'login_at',
        'logout_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate the duration of the session in seconds.
     */
    public function getDurationInSecondsAttribute(): int
    {
        $end = $this->logout_at ?? now();
        // Use abs() to ensure we don't get negative values if timestamps are slightly off
        return (int) abs($end->diffInSeconds($this->login_at));
    }

    /**
     * Get the human-readable duration.
     */
    public function getDurationHumanAttribute(): string
    {
        $seconds = $this->duration_in_seconds;
        
        if ($seconds < 60) {
            return $seconds . 's';
        }
        
        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return $minutes . 'm';
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        return $hours . 'h:' . $remainingMinutes . 'm';
    }
}
