<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'skill_name',
        'tradie_id',
    ];

    public function tradie()
    {
        return $this->belongsTo(Tradie::class);
    }
}
