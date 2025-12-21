<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReviewReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'reporter_type',
        'reporter_id',
        'reason',
        'description',
        'status',
        'admin_notes',
    ];

    // ─── Relationships ───────────────────────────────────
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    // Polymorphic relationship - reporter can be Homeowner or Tradie
    public function reporter(): MorphTo
    {
        return $this->morphTo();
    }

    // Helper method to get reporter details
    public function getReporterNameAttribute()
    {
        if ($this->reporter_type === 'App\\Models\\Homeowner') {
            $reporter = Homeowner::find($this->reporter_id);
        } else {
            $reporter = Tradie::find($this->reporter_id);
        }

        return $reporter ? $reporter->full_name : 'Unknown';
    }

    // ─── Scopes ──────────────────────────────────────────
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}