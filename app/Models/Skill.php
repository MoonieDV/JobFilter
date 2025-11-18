<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'aliases',
        'popularity_score',
    ];

    protected $casts = [
        'aliases' => 'array',
        'popularity_score' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(SkillCategory::class, 'category_id');
    }

    public function userSkills()
    {
        return $this->hasMany(UserSkill::class);
    }
}
