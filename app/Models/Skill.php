<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['skill_name', 'skill_description'];

    // public function tradies()
    // {
    //     return $this->belongsToMany(Tradie::class, 'tradie_skills');
    // }
}
