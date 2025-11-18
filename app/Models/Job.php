<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'company_name',
        'location',
        'employment_type',
        'experience_level',
        'salary',
        'description',
        'responsibilities',
        'requirements',
        'required_skills',
        'preferred_skills',
        'status',
        'posted_by',
        'published_at',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'required_skills' => 'array',
        'preferred_skills' => 'array',
        'published_at' => 'datetime',
    ];

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'open');
    }
}
