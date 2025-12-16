<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'homeowner_id',
        'job_category_id',
        'title',
        'description',
        'job_type',
        'status',
        'budget',
        'location',
        'latitude',
        'longitude',
        'scheduled_at',
    ];

    // Relationships
    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class);
    }

    public function category()
    {
        return $this->belongsTo(JobCategories::class, 'job_category_id');
    }

    // add relationship to home_owner if needed
    public function jobrequest()
    { 
        return $this->belongsTo(Homeowner::class);
    }

    
}