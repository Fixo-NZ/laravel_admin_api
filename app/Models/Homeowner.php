<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
<<<<<<< HEAD
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Homeowner extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
=======
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as AuthMustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Booking;
use App\Notifications\HomeownerVerifyEmail;

class Homeowner extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, AuthMustVerifyEmail;
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e

    // ─── Fillable ────────────────────────────────────────────────
    // These are the attributes you can mass assign (e.g., Homeowner::create()).
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

    // ─── Hidden ─────────────────────────────────────────────────
    // These attributes will not be visible when the model is converted to arrays or JSON.
    protected $hidden = [
        'password',
<<<<<<< HEAD
        'remember_token',
=======
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
    ];

    // ─── Casts ──────────────────────────────────────────────────
    // These define how certain attributes are automatically converted.
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

<<<<<<< HEAD
=======
    public function routeNotificationForMail(Notification $notification): array|string
    {
        return [$this->email => $this->first_name . ' ' . $this->last_name];
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new HomeownerVerifyEmail());
    }

>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
    // ─── Boot Method ────────────────────────────────────────────
    // Automatically sets default status to 'active' when a new homeowner is created.
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($homeowner) {
            if (empty($homeowner->status)) {
                $homeowner->status = 'active';
            }
        });
    }

    // ─── Scopes ────────────────────────────────────────────────
    // Allow cleaner queries such as Homeowner::active()->get();
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
<<<<<<< HEAD
=======
    public function user()
    {
        return $this->belongsTo(User::class);
    }
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e

    // Filter homeowners by region.
    public function scopeInRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    // Find nearby homeowners based on coordinates (latitude and longitude).
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
<<<<<<< HEAD
        ->having('distance', '<=', $radiusKm)
        ->orderBy('distance');
=======
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
    }

    // ─── Accessors ────────────────────────────────────────────────
    // Combines address components into one readable string.
    public function getFullAddressAttribute()
    {
        return collect([$this->address, $this->city, $this->region, $this->postal_code])
            ->filter()
            ->implode(', ');
    }

<<<<<<< HEAD
=======
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    
    public function jobOffers()
    {
        return $this->hasMany(HomeownerJobOffer::class);
    }

>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
    // ─── Relationships (commented out as requested) ───────────────
    // These are kept here for later use but are currently disabled.

    // public function bookings()
    // {
    //     return $this->hasMany(Booking::class);
    // }

<<<<<<< HEAD
=======
    // Alias for bookings used by the admin UI (keeps template naming `jobs`)
    public function jobs()
    {
        return $this->hasMany(Booking::class, 'homeowner_id');
    }

>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
    // public function favoriteTradies()
    // {
    //     return $this->belongsToMany(Tradie::class, 'user_favorites', 'user_id', 'favorited_user_id');
    // }

<<<<<<< HEAD
    // ─── Relationships ───────────────────────────────────
public function reviewsGiven()
{
    return $this->hasMany(Review::class, 'homeowner_id');
}

// Accessor for full name (for Filament display)
    public function getFullNameAttribute()
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);
        return implode(' ', $parts);
    }
=======
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
}
