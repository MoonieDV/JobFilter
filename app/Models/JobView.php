<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobView extends Model
{
    protected $fillable = [
        'job_id',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
