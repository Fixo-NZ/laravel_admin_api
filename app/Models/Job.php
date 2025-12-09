<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'homeowner_id',
        'tradie_id',
        'title',
        'description',
        'status',
        'price',
        'user_id',
        'provider_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relationships
    public function homeowner(): BelongsTo
    {
        return $this->belongsTo(Homeowner::class);
    }

    public function tradie(): BelongsTo
    {
        return $this->belongsTo(Tradie::class);
    }

    // Backward compatibility relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}