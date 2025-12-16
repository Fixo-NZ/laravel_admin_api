<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JobRequest;
use App\Models\JobCategories;
use App\Models\Tradie;

class TradieJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_request_id',
        'job_category_id',
        'tradie_id',
        'status',
        'budget',
        'scheduled_at',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'scheduled_at' => 'datetime',
    ];

    /* =======================
       🔗 Relationships
       ======================= */

    /**
     * The job request this tradie job fulfills.
     */
    public function jobRequest()
    {
        return $this->belongsTo(JobRequest::class);
    }

    /**
     * The category of this job.
     */
    public function jobCategory()
    {
        return $this->belongsTo(JobCategories::class);
    }

    /**
     * The tradie assigned to this job (nullable).
     */
    public function tradie()
    {
        return $this->belongsTo(Tradie::class);
    }

    /* =======================
       🔍 Query Scopes
       ======================= */

    /**
     * Scope for filtering by status (pending, accepted, completed).
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for jobs that are scheduled in the future.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now());
    }

    /**
     * Scope for jobs assigned to a specific tradie.
     */
    public function scopeForTradie($query, int $tradieId)
    {
        return $query->where('tradie_id', $tradieId);
    }

    /**
     * Scope for jobs related to a specific job request.
     */
    public function scopeForRequest($query, int $jobRequestId)
    {
        return $query->where('job_request_id', $jobRequestId);
    }
}