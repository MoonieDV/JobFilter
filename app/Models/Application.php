<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'job_id',
        'applicant_id',
        'employer_id',
        'full_name',
        'email',
        'phone',
        'location',
        'resume_path',
        'cover_letter',
        'extracted_skills',
        'status',
        'match_score',
        'applied_at',
        'interview_scheduled_at',
        'interview_type',
    ];

    protected $casts = [
        'extracted_skills' => 'array',
        'match_score' => 'decimal:2',
        'applied_at' => 'datetime',
        'interview_scheduled_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function questions()
    {
        return $this->hasMany(ApplicationQuestion::class);
    }

    public function interviewSchedule()
    {
        return $this->hasOne(InterviewSchedule::class);
    }
}
