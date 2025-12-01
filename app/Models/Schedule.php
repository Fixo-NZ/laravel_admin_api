<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{

    public function homeowner()
{
    return $this->belongsTo(Homeowner::class);
}


    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'job_title',      // Added
        'duration',       // Added
        'date',            // Added
        'start_time',
        'end_time',
        'color',
        'status',
        'rescheduled_at',
    ];

    /**
     * Reschedule this schedule to a new time range.
     */
    public function reschedule($newStart, $newEnd)
    {
        $this->update([
            'start_time'     => $newStart,
            'end_time'       => $newEnd,
            'status'         => 'rescheduled',
            'rescheduled_at' => Carbon::now(),
        ]);
    }

    /**
     * Cancel this schedule.
     */
    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    
}
