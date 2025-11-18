<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSkill extends Model
{
    protected $fillable = [
        'user_id',
        'skill_id',
        'skill_name',
        'confidence_score',
        'extracted_from',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
