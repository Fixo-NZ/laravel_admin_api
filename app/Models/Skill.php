<?php

namespace App\Models;

<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
>>>>>>> 24172d873ef38a8fa72e08a82046ccf88c100ee2
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
<<<<<<< HEAD
    protected $fillable = ['skill_name', 'skill_description'];

    // public function tradies()
    // {
    //     return $this->belongsToMany(Tradie::class, 'tradie_skills');
    // }
=======
    use HasFactory;

    protected $fillable = [
        'skill_name',
        'tradie_id',
    ];

    public function tradie()
    {
        return $this->belongsTo(Tradie::class);
    }
>>>>>>> 24172d873ef38a8fa72e08a82046ccf88c100ee2
}
