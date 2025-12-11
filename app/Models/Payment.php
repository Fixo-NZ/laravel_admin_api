<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
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

    /**
     * Hide sensitive fields from JSON serialization by default.
     */
    protected $hidden = [
        'payment_method_id',
        'card_brand',
        'card_last4number',
        'exp_month',
        'exp_year',
        'created_at',
        'updated_at',
    ];
    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class);
    }
}

