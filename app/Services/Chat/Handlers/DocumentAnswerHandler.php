<?php

namespace App\Services\Chat\Handlers;

use App\Models\Chat;
use App\Models\AiLog;
use App\Models\Document;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class DocumentAnswerHandler
{
    public function handle(Chat $chat, string $question, array $matchedTitles, string $originalLang)
    {
        // 1. Загружаем документы
        $docs = Document::whereIn('title', $matchedTitles)->get();

        $contextText = '';
        foreach ($docs as $doc) {
            $contextText .= "### {$doc->title}\n{$doc->text}\n\n";
        }

        // 2. История чата
        $history = $chat->messages->map(fn($m) => [
            'role' => $m->role,
            'content' => $m->content,
        ])->toArray();

        // 3. Финальный запрос к GPT
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => array_merge(
                [
                    [
                        'role' => 'system',
                        'content' =>
                        "Ты ассистент TeachAI.\n" .
                            "Используй ТОЛЬКО следующий контекст.\n" .
                            "Ответь на том же языке, на котором был задан вопрос пользователя ({$originalLang}).\n" .
                            "Если ответа в контексте нет — скажи, что информация не найдена.\n\n" .
                            $contextText
                    ]
                ],
                $history,
                [
                    [
                        'role' => 'user',
                        'content' => $question
                    ]
                ]
            )
        ]);

        $answer = $response->choices[0]->message->content;

        // 4. Сохраняем ответ
        $chat->messages()->create([
            'role' => 'assistant',
            'content' => $answer,
        ]);

        AiLog::create([
            'user_id' => auth()->id(),
            'chat_id' => $chat->id,
            'question' => $question,
            'matched_titles' => $matchedTitles,
            'context' => $contextText,
            'final_answer' => $answer,
            'error' => null,
            'duration_ms' => (microtime(true) - LARAVEL_START) * 1000,
        ]);

        return response()->json([
            'answer' => $answer,
        ]);
    }
}
