<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $guarded = [];

    protected $appends = ['sender_name'];

    /**
     * Get the sender's display name.
     */
    public function getSenderNameAttribute()
    {
        try {
            if ($this->sender_type && str_contains($this->sender_type, 'User')) {
                $user = \App\Models\User::find($this->sender_id);
                return $user ? $user->full_name : 'System';
            }

            if ($this->sender_type && str_contains($this->sender_type, 'Guest')) {
                $guest = \App\Models\ChatGuest::find($this->sender_id);
                return $guest ? $guest->email : 'Guest';
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return 'Guest';
    }
}
