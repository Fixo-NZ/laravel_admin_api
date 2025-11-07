<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'homeowner_id',
        'tradie_id',
        'rating',
        'feedback',
        'service_quality_rating',
        'service_quality_comment',
        'performance_rating',
        'performance_comment',
        'contractor_service_rating',
        'response_time_rating',
        'best_feature',
        'images',
        'show_username',
        'helpful_count',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
        'show_username' => 'boolean',
        'helpful_count' => 'integer',
        'rating' => 'integer',
        'service_quality_rating' => 'integer',
        'performance_rating' => 'integer',
        'contractor_service_rating' => 'integer',
        'response_time_rating' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function homeowner(): BelongsTo
    {
        return $this->belongsTo(Homeowner::class);
    }

    public function tradie(): BelongsTo
    {
        return $this->belongsTo(Tradie::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }

    // ─── Scopes ──────────────────────────────────────────
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForTradie($query, $tradieId)
    {
        return $query->where('tradie_id', $tradieId);
    }

    public function scopeForHomeowner($query, $homeownerId)
    {
        return $query->where('homeowner_id', $homeownerId);
    }

    // ─── Static Methods ──────────────────────────────────
    public static function getTradieAverageRating($tradieId)
    {
        return static::forTradie($tradieId)
            ->approved()
            ->avg('rating');
    }

    public static function getTradieReviewCount($tradieId)
    {
        return static::forTradie($tradieId)
            ->approved()
            ->count();
    }

    public static function getTradieRatingBreakdown($tradieId)
    {
        return static::forTradie($tradieId)
            ->approved()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderByDesc('rating')
            ->pluck('count', 'rating')
            ->toArray();
    }
}