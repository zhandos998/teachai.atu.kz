<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiLog extends Model
{
    protected $fillable = [
        'user_id',
        'chat_id',
        'question',
        'matched_titles',
        'context',
        'final_answer',
        'error',
        'duration_ms'
    ];

    protected $casts = [
        'matched_titles' => 'array',
    ];
}
