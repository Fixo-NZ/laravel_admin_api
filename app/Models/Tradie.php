<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Tradie extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'firebase_uid',
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
        'business_name',
        'license_number',
        'insurance_details',
        'years_experience',
        'hourly_rate',
        'availability_status',
        'service_radius',
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
        'hourly_rate' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'available');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeInRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    public function scopeWithService($query, $serviceId)
    {
        return $query->whereHas('services', function ($q) use ($serviceId) {
            $q->where('service_id', $serviceId);
        });
    }

    public function scopeNearLocation($query, $latitude, $longitude, $radiusKm = null)
    {
        $radius = $radiusKm ?? 50;

        return $query->selectRaw("
            *, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance
        ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
    }

    public function scopeWithinServiceRadius($query, $latitude, $longitude)
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
            ->whereRaw('(
            6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude))
            )
        ) <= service_radius', [$latitude, $longitude, $latitude])
            ->orderBy('distance');
    }

    // Accessors
    public function getFullAddressAttribute()
    {
        return collect([$this->address, $this->city, $this->region, $this->postal_code])
            ->filter()
            ->implode(', ');
    }

    public function getIsVerifiedAttribute()
    {
        return !is_null($this->verified_at);
    }

    public function getAverageRatingAttribute()
    {
        return $this->receivedReviews()->avg('rating') ?? 0;
    }

    public function getTotalReviewsAttribute()
    {
        return $this->receivedReviews()->count();
    }

    // Relationships
    public function services()
    {
        return $this->belongsToMany(Service::class, 'tradie_services')
            ->withPivot('base_rate')
            ->withTimestamps();
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
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

    public function favoriteHomeowners()
    {
        return $this->belongsToMany(Homeowner::class, 'user_favorites', 'favorited_user_id', 'user_id');
    }

    // Chat relationships
    public function chatsAsParticipant1()
    {
        return $this->hasMany(Chat::class, 'participant_1_id')->where('participant_1_type', 'tradie');
    }

    public function chatsAsParticipant2()
    {
        return $this->hasMany(Chat::class, 'participant_2_id')->where('participant_2_type', 'tradie');
    }

    public function allChats()
    {
        return Chat::where(function ($query) {
            $query->where('participant_1_id', $this->id)->where('participant_1_type', 'tradie');
        })->orWhere(function ($query) {
            $query->where('participant_2_id', $this->id)->where('participant_2_type', 'tradie');
        });
    }

    public function sentChatMessages()
    {
        return $this->hasMany(Message::class, 'sender_id')->where('sender_type', 'tradie');
    }

    public function receivedChatMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id')->where('receiver_type', 'tradie');
    }
}
