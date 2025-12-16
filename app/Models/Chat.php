<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'firebase_chat_id',
        'participant_1_uid',
        'participant_2_uid',
        'participant_1_type',
        'participant_2_type',
        'participant_1_id',
        'participant_2_id',
        'last_message',
        'last_sender_uid',
        'last_message_at',
        'is_active',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get all messages for this chat
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('sent_at', 'desc');
    }

    /**
     * Get the latest message
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany('sent_at');
    }

    /**
     * Get participant 1 (homeowner or tradie)
     */
    public function participant1()
    {
        if ($this->participant_1_type === 'homeowner') {
            return $this->belongsTo(Homeowner::class, 'participant_1_id');
        }
        return $this->belongsTo(Tradie::class, 'participant_1_id');
    }

    /**
     * Get participant 2 (homeowner or tradie)
     */
    public function participant2()
    {
        if ($this->participant_2_type === 'homeowner') {
            return $this->belongsTo(Homeowner::class, 'participant_2_id');
        }
        return $this->belongsTo(Tradie::class, 'participant_2_id');
    }

    /**
     * Scope to get chats for a specific user
     */
    public function scopeForUser($query, $firebaseUid)
    {
        return $query->where(function ($q) use ($firebaseUid) {
            $q->where('participant_1_uid', $firebaseUid)
              ->orWhere('participant_2_uid', $firebaseUid);
        });
    }

    /**
     * Scope to get active chats
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the other participant's data
     */
    public function getOtherParticipant($currentUserUid)
    {
        if ($this->participant_1_uid === $currentUserUid) {
            return [
                'uid' => $this->participant_2_uid,
                'type' => $this->participant_2_type,
                'id' => $this->participant_2_id,
                'model' => $this->participant_2_type === 'homeowner' 
                    ? $this->participant2() 
                    : $this->participant2()
            ];
        }
        
        return [
            'uid' => $this->participant_1_uid,
            'type' => $this->participant_1_type,
            'id' => $this->participant_1_id,
            'model' => $this->participant_1_type === 'homeowner' 
                ? $this->participant1() 
                : $this->participant1()
        ];
    }

    /**
     * Generate Firebase chat ID from two UIDs
     */
    public static function generateFirebaseChatId($uid1, $uid2)
    {
        $uids = [$uid1, $uid2];
        sort($uids);
        return implode('-', $uids);
    }

    /**
     * Get unread message count for a user
     */
    public function getUnreadCountForUser($firebaseUid)
    {
        return $this->messages()
            ->where('receiver_firebase_uid', $firebaseUid)
            ->where('is_read', false)
            ->count();
    }
}