<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeownerJobOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'homeowner_id',
        'service_category_id',
        'job_type',
        'frequency',
        'preferred_date',
        'start_date',
        'end_date',
        'title',
        'job_size',
        'description',
        'address',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Belongs to the homeowner who posted the job
    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class);
    }

    // Category of the job (e.g. Plumbing, Electrical)
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    // Many-to-many relationship with services (e.g. Fix Leak, Install Sink)
    public function services()
    {
        return $this->belongsToMany(Service::class, 'homeowner_job_offer_services', 'job_offer_id', 'service_id');
    }

    // Job photos (up to 5 images)
    public function photos()
    {
        return $this->hasMany(JobOfferPhoto::class, 'job_offer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    // Returns full URLs for photos when serialized
    protected $appends = ['photo_urls'];

    public function getPhotoUrlsAttribute()
    {
        return $this->photos->map(fn ($photo) => asset('storage/' . $photo->file_path))->toArray();
    }
}
