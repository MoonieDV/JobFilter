<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];
}
