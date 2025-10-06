<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'status',
    ];

    // Assuming a job belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
