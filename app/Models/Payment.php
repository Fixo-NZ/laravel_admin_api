<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'homeowner_id',
        'booking_id',
        'customer_id',
        'payment_method_id',
        'amount',
        'currency',
        'status',
        'card_brand',
        'card_last4number',
        'exp_month',
        'exp_year',
    ];

    /**
     * Hide sensitive fields from JSON serialization by default.
     */
    protected $hidden = [
        'customer_id',
        'payment_method_id',
        'card_brand',
        'card_last4number',
        'exp_month',
        'exp_year',
        'created_at',
        'updated_at',
    ];

        public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class);
    }
}
