<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'homeowner_id',
        'job_categoryid',
        'job_description',
        'location',
        'status',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'InProgress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'Cancelled');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('job_categoryid', $categoryId);
    }

    // Relationships
    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'job_categoryid');
    }

    public function tradies()
    {
        return $this->belongsToMany(Tradie::class, 'tradie_services')
            ->withPivot('base_rate')
            ->withTimestamps();
    }
}
