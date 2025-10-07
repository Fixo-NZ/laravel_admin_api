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

    public function category()
    {
        return $this->belongsTo(Category::class, 'job_categoryid');
    }

<<<<<<< HEAD
    // public function jobs()
    // {
    //     return $this->hasMany(Job::class);
    // }
=======
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
>>>>>>> bf01661 (Refracted jobs table to service table to be able to accompany with other groups. Adjusted unit testing and passed all.)

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
