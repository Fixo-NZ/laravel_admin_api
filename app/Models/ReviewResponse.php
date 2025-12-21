<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'tradie_id',
        'content',
        'edited_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function tradie(): BelongsTo
    {
        return $this->belongsTo(Tradie::class);
    }
}
