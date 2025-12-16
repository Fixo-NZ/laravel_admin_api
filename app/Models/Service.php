<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Relationships
    public function tradies()
    {
        return $this->belongsToMany(Tradie::class, 'tradie_services')
            ->withPivot('base_rate')
            ->withTimestamps();
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    // Static methods
    public static function getCategories()
    {
        return self::active()
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();
    }
}
