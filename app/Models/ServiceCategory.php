<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
