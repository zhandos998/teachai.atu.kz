<?php

namespace App\Services\Chat;

use App\Models\AiLog;
use App\Models\Chat;
use App\Models\Document;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class PromptFactory
{
    public static function titleClassifier(string $titles, string $question): array
    {
        return [
            [
                'role' => 'system',
                'content' => view('prompts.title_classifier', [
                    'titles' => $titles
                ])->render()
            ],
            [
                'role' => 'user',
                'content' => $question
            ]
        ];
    }
}
