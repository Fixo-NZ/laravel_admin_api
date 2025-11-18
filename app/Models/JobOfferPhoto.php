<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOfferPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_offer_id',
        'file_path',
        'original_name',
        'file_size',
    ];

    protected $appends = ['url'];

    public function jobOffer()
    {
        return $this->belongsTo(HomeownerJobOffer::class, 'job_offer_id');
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
