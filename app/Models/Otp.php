<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'otp_code',
        'is_verified',
        'expires_at',
    ];
    
    protected $casts = [
        'is_used' => 'boolean',
        'is_verified' => 'boolean',
        'expires_at' => 'datetime',
    ];
}
