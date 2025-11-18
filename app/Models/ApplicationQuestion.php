<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationQuestion extends Model
{
    protected $fillable = [
        'application_id',
        'question_id',
        'question_text',
        'answer',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
