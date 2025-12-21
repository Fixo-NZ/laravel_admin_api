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
<<<<<<< HEAD
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
=======
        'category_id',
        'status',
    ];

    protected $casts = [
       
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
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

<<<<<<< HEAD
    // Static methods
    public static function getCategories()
    {
        return self::active()
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();
=======

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
    }
}
