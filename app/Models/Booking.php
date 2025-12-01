<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'homeowner_id', 'tradie_id', 'service_id',
        'booking_start', 'booking_end', 'status', 'total_price'
    ];

    public function homeowner() {
        return $this->belongsTo(Homeowner::class);
    }

    public function tradie() {
        return $this->belongsTo(Tradie::class);
    }

    public function service() {
        return $this->belongsTo(Service::class);
    }

    public function logs() {
        return $this->hasMany(BookingLog::class);
    }
}