<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
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
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    // ─── Casts ──────────────────────────────────────────────────
    // These define how certain attributes are automatically converted.
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

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
    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }

    // ─── Accessors ────────────────────────────────────────────────
    // Combines address components into one readable string.
    public function getFullAddressAttribute()
    {
        return collect([$this->address, $this->city, $this->region, $this->postal_code])
            ->filter()
            ->implode(', ');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    
    public function jobOffers()
    {
        return $this->hasMany(HomeownerJobOffer::class);
    }

    // ─── Relationships (commented out as requested) ───────────────
    // These are kept here for later use but are currently disabled.

    // public function bookings()
    // {
    //     return $this->hasMany(Booking::class);
    // }

    // Alias for bookings used by the admin UI (keeps template naming `jobs`)
    public function jobs()
    {
        return $this->hasMany(Booking::class, 'homeowner_id');
    }

    // public function favoriteTradies()
    // {
    //     return $this->belongsToMany(Tradie::class, 'user_favorites', 'user_id', 'favorited_user_id');
    // }

}
