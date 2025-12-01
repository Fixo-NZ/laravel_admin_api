<?php

namespace App\Models;

<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
>>>>>>> 24172d873ef38a8fa72e08a82046ccf88c100ee2
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
<<<<<<< HEAD
    //
=======
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
>>>>>>> 24172d873ef38a8fa72e08a82046ccf88c100ee2
}
