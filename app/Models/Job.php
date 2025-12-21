<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model
{
    use HasFactory;

    protected $table = 'business_jobs';

    protected $fillable = [
        'user_id',
        'provider_id',
        'title',
        'description',
        'status',
        'price',
        // Add other fields as needed
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relationships
    public function homeowner(): BelongsTo
    {
        return $this->belongsTo(Homeowner::class, 'user_id');
    }

    public function tradie(): BelongsTo
    {
        return $this->belongsTo(Tradie::class, 'provider_id');
    }

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

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
=======
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    //
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
}
