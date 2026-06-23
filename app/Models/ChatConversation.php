<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ChatConversation extends Model
{
    protected $guarded = [];

    // Expose computed unread count in JSON serialization
    protected $appends = ['unread_count'];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function guest()
    {
        return $this->belongsTo(ChatGuest::class, 'created_by');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    /**
     * Count of unread guest (non-admin) messages in this conversation.
     */
    public function getUnreadCountAttribute()
    {
        return $this->messages()
            ->where('is_read', false)
            ->where('sender_type', '!=', User::class)
            ->count();
    }
}
