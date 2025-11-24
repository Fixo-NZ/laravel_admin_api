<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    
    protected $fillable = [
        'homeowner_id',
        'payment_method_id',
        'amount',
        'currency',
        'status',
        'card_brand',
        'card_last4number',
        'exp_month',
        'exp_year'
    ];
}
