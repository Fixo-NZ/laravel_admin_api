<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Homeowner extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'password',
        'avatar',
        'bio',
        'address',
        'city',
        'region',
        'postal_code',
        'latitude',
        'longitude',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    public function scopeNearLocation($query, $latitude, $longitude, $radiusKm = 50)
    {
        return $query->selectRaw("
            *, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance
        ", [$latitude, $longitude, $latitude])
        ->having('distance', '<=', $radiusKm)
        ->orderBy('distance');
    }

    // Accessors
    public function getFullAddressAttribute()
    {
        return collect([$this->address, $this->city, $this->region, $this->postal_code])
            ->filter()
            ->implode(', ');
    }

    // Relationships
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function favoriteTradies()
    {
        return $this->belongsToMany(Tradie::class, 'user_favorites', 'user_id', 'favorited_user_id');
    }

    // Chat relationships
    public function chatsAsParticipant1()
    {
        return $this->hasMany(Chat::class, 'participant_1_id')->where('participant_1_type', 'homeowner');
    }

    public function chatsAsParticipant2()
    {
        return $this->hasMany(Chat::class, 'participant_2_id')->where('participant_2_type', 'homeowner');
    }

    public function allChats()
    {
        return Chat::where(function ($query) {
            $query->where('participant_1_id', $this->id)->where('participant_1_type', 'homeowner');
        })->orWhere(function ($query) {
            $query->where('participant_2_id', $this->id)->where('participant_2_type', 'homeowner');
        });
    }

    public function sentChatMessages()
    {
        return $this->hasMany(Message::class, 'sender_id')->where('sender_type', 'homeowner');
    }

    public function receivedChatMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id')->where('receiver_type', 'homeowner');
    }
}
