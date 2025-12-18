<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class TradieComplaint extends Model
{

    use HasFactory;

    protected $fillable = [
        'tradie_id',
        'homeowner_id',
        'title',
        'description',
        'status',
    ];

    public function tradie()
    {
        return $this->belongsTo(Tradie::class);
    }

    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class);
    }
}
