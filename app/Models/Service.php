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
        'category_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function tradies()
    {
        return $this->belongsToMany(Tradie::class, 'tradie_services')
            ->withPivot('base_rate')
            ->withTimestamps();
    }

    // public function jobs()
    // {
    //     return $this->hasMany(Job::class);
    // }


    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
