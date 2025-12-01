<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrgentBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'homeowner_id',
        'job_id',
        'tradie_id',
        'status',
        'priority_level',
        'requested_at',
        'responded_at',
        'notes',
        'service_name',
        'preferred_date',
        'preferred_time_window',
        'contact_name',
        'contact_email',
        'contact_phone',
        'address',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class);
    }

    public function tradie()
    {
        return $this->belongsTo(Tradie::class);
    }
}


