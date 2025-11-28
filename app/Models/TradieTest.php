<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class TradieTest extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'password',
        'confirm_password',
    ];

    protected $hidden = [
        'password',
        'confirm_password',
    ];
}