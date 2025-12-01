<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'user_id', 'action', 'notes'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
