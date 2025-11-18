<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillCategory extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'description',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function skills()
    {
        return $this->hasMany(Skill::class, 'category_id');
    }
}
