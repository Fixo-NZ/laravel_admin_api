<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $primaryKey = 'job_id';
    public $timestamps = false;

    protected $fillable = [
        'homeowner_id',
        'job_categoryid',
        'job_description',
        'location',
        'status',
        'createdAt',
        'updatedAt',
        'rating',
    ];

    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class, 'homeowner_id');
    }

    // public function category()
    // {
    //     return $this->belongsTo(Category::class, 'job_categoryid');
    // }

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
