<?php

namespace App\Services\Chat;

use App\Models\AiLog;
use App\Models\Chat;
use App\Models\Document;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class TitleClassifier
{
    public function classify(string $question): array
    {
        $titles = Document::pluck('title')->toArray();
        // $message = PromptFactory::titleClassifier($titles, $question);

        $prompt = view('prompts.title_classifier', [
            'titles' => $titles
        ])->render();


        // Log::info('FINAL SYSTEM PROMPT', [$prompt]);
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt
                ],
                [
                    'role' => 'user',
                    'content' => $question
                ]
            ]
        ]);

        return json_decode(
            $response->choices[0]->message->content,
            true
        ) ?? [];
    }
}
