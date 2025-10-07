<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    // This Service model now represents records stored in the `jobs` table
    protected $table = 'jobs';

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tradies()
    {
        return $this->belongsToMany(Tradie::class, 'tradie_services')
            ->withPivot('base_rate')
            ->withTimestamps();
    }

    // Convenience scope for open jobs/services
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
