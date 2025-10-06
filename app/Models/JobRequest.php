<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'homeowner_id',
        'service_type',
        'location',
        'budget',
        'description',
    ];

    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class);
    }
}
