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
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'professional_bio',
        'password',
        'avatar',
        'bio',
        'portfolio_images',
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
        'working_hours',
        'emergency_available',
        'availability_calendar',
        'service_radius',
        'status',
        'profile_completed',
        'profile_completed_at'
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
        'portfolio_images' => 'array',
        'working_hours' => 'array',
        'availability_calendar' => 'array',
        'emergency_available' => 'boolean',
        'profile_completed' => 'boolean',
        'profile_completed_at' => 'datetime',
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
}
