<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel; // Import the Panel class
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'password',
        'role',
        'status',
        'phone',
        'location',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // =========================================================================
    // Problem : Filament expects a 'name' attribute, but our User model uses 'first_name' and 'last_name'.
    // This causes issues in the Filament admin panel where it tries to display the user's name
    // and as such filament displays as Filament User
    // =========================================================================


    // =========================================================================
    // FIX 1: DEFINE A VIRTUAL 'name' ATTRIBUTE FOR FILAMENT
    // This combines first_name and last_name, which is what Filament expects.
    // =========================================================================
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name . ' ' . $this->last_name,
        );
    }
    
    // =========================================================================
    // FIX 2: Implement getFilamentName() correctly (Optional but good practice)
    // This is used by Filament when it needs the user's name specifically.
    // We can rely on the 'name' attribute we just created.
    // =========================================================================
    public function getFilamentName(): string
    {
        // $this->name will now call the accessor above
        return $this->name;
    }

    // =========================================================================
    // FILAMENT ACCESS CONTROL
    // =========================================================================
    public function canAccessPanel(Panel $panel): bool
    {
        // For a basic setup, returning true allows all logged-in users.
        // If you need role-based access, implement it here (e.g., return $this->role === 'admin';)
        return true;
    }

    // --- Relationships for Homeowner modal ---
    // (Keep your relationships here if they were part of the original model)
}