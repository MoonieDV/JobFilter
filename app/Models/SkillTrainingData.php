<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillTrainingData extends Model
{
    protected $table = 'skill_training_data';

    protected $fillable = [
        'skill_name',
        'category',
        'frequency',
        'aliases',
    ];

    protected $casts = [
        'aliases' => 'array',
    ];
}
