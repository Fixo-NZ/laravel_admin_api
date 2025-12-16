<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'firebase_message_id',
        'firebase_chat_id',
        'chat_id',
        'sender_firebase_uid',
        'receiver_firebase_uid',
        'sender_id',
        'receiver_id',
        'sender_type',
        'receiver_type',
        'message',
        'is_read',
        'sent_at',
        'read_at',
        'metadata',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the chat this message belongs to
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the sender (homeowner or tradie)
     */
    public function sender()
    {
        if ($this->sender_type === 'homeowner') {
            return $this->belongsTo(Homeowner::class, 'sender_id');
        }
        return $this->belongsTo(Tradie::class, 'sender_id');
    }

    /**
     * Get the receiver (homeowner or tradie)
     */
    public function receiver()
    {
        if ($this->receiver_type === 'homeowner') {
            return $this->belongsTo(Homeowner::class, 'receiver_id');
        }
        return $this->belongsTo(Tradie::class, 'receiver_id');
    }

    /**
     * Scope to get messages for a specific chat
     */
    public function scopeForChat($query, $chatId)
    {
        return $query->where('chat_id', $chatId);
    }

    /**
     * Scope to get unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get messages for a specific user
     */
    public function scopeForUser($query, $firebaseUid)
    {
        return $query->where(function ($q) use ($firebaseUid) {
            $q->where('sender_firebase_uid', $firebaseUid)
              ->orWhere('receiver_firebase_uid', $firebaseUid);
        });
    }

    /**
     * Scope to get messages between two users
     */
    public function scopeBetweenUsers($query, $uid1, $uid2)
    {
        return $query->where(function ($q) use ($uid1, $uid2) {
            $q->where(function ($subQ) use ($uid1, $uid2) {
                $subQ->where('sender_firebase_uid', $uid1)
                     ->where('receiver_firebase_uid', $uid2);
            })->orWhere(function ($subQ) use ($uid1, $uid2) {
                $subQ->where('sender_firebase_uid', $uid2)
                     ->where('receiver_firebase_uid', $uid1);
            });
        });
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Check if message is sent by specific user
     */
    public function isSentBy($firebaseUid)
    {
        return $this->sender_firebase_uid === $firebaseUid;
    }

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimeAttribute()
    {
        return $this->sent_at->diffForHumans();
    }
}