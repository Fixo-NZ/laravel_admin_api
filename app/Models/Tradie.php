<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
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
        'trade_type',
        'insurance_details',
        'years_experience',
        'hourly_rate',
        'availability_status',
        'service_radius',
        'status',
        'suspension_reason',
        'suspension_start',
        'suspension_end'
    ];

    protected $hidden = [
        'password',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'hourly_rate' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    // ─── Boot Method ────────────────────────────────────────────
    // Automatically calculate hourly rates for each tradie upon creation based on their years of experience
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tradie) {
            if (empty($tradie->hourly_rate)) {
                $tradie->hourly_rate = self::calculateHourlyRate($tradie);
            }
        });

        static::updating(function ($tradie) {
            $fieldsToCheck = [
                'years_experience',
                'region',
                'license_number',
                'insurance_details',
                'availability_status',
                'service_radius',
                'trade_type'
            ];

            if ($tradie->isDirty($fieldsToCheck)) {
                $tradie->hourly_rate = self::calculateHourlyRate($tradie);
            }
        });
    }

    // =========================================================================
    // AUTO RATE CALCULATION LOGIC
    // =========================================================================

    /**
     * Automatically calculate a fair hourly rate (in NZD)
     * based on tradie experience, trade type, and other factors.
     */
    public static function calculateHourlyRate(self $tradie): float
    {
        $baseRate = 25.00; // Starting baseline in NZD/hour
        $rate = $baseRate;

        // 1. Experience bonus (max +NZ$40 for 20+ years)
        $rate += min($tradie->years_experience ?? 0, 20) * 2.0;

        // 2. Trade type influence
        $tradeMultipliers = [
            'electrical'          => 1.6,
            'plumbing'            => 1.5,
            'hvac'                => 1.45,
            'carpentry'           => 1.3,
            'roofing'             => 1.3,
            'painting'            => 1.2,
            'masonry'             => 1.25,
            'flooring'            => 1.2,
            'fencing & decking'   => 1.2,
            'appliance repair'    => 1.15,
            'drywall & plastering' => 1.15,
            'window & door'       => 1.1,
            'pest control'        => 1.1,
            'gardening'           => 1.05,
        ];

        if (!empty($tradie->trade_type) && isset($tradeMultipliers[strtolower($tradie->trade_type)])) {
            $rate *= $tradeMultipliers[strtolower($tradie->trade_type)];
        }

        // 3. Region multiplier (urban tradies earn more)
        if (in_array(strtolower($tradie->region), ['auckland', 'wellington', 'christchurch'])) {
            $rate *= 1.25;
        } elseif (in_array(strtolower($tradie->region), ['hamilton', 'tauranga', 'dunedin'])) {
            $rate *= 1.1;
        }

        // 4. License bonus
        if (!empty($tradie->license_number)) {
            $rate += 10;
        }

        // 5. Insurance bonus
        if (!empty($tradie->insurance_details)) {
            $rate += 5;
        }

        // 6. Availability bump (if busy, increase slightly)
        if ($tradie->availability_status === 'busy') {
            $rate += 5;
        }

        // 7. Service radius adjustment (+NZ$1 per 10 km beyond 50)
        if ($tradie->service_radius > 50) {
            $extraKm = $tradie->service_radius - 50;
            $rate += floor($extraKm / 10) * 1;
        }

        return round($rate, 2);
    }

    public function routeNotificationForMail(Notification $notification): array|string
    {
        return [$this->email => $this->first_name . ' ' . $this->last_name];
    }

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

    // public function jobApplications()
    // {
    //     return $this->hasMany(JobApplication::class);
    // }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // public function sentMessages()
    // {
    //     return $this->hasMany(Message::class, 'sender_id');
    // }

    // public function receivedMessages()
    // {
    //     return $this->hasMany(Message::class, 'receiver_id');
    // }

    // public function reviews()
    // {
    //     return $this->hasMany(Review::class, 'reviewer_id');
    // }

    // public function receivedReviews()
    // {
    //     return $this->hasMany(Review::class, 'reviewee_id');
    // }

    // public function favoriteHomeowners()
    // {
    //     return $this->belongsToMany(Homeowner::class, 'user_favorites', 'favorited_user_id', 'user_id');
    // }
}
