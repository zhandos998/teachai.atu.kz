<?php

namespace App\Services\Chat\Handlers;

use App\Models\Chat;
use App\Models\AiLog;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class SmallTalkHandler
{
    public function handle(Chat $chat, string $question, string $originalLang)
    {
        // История диалога
        $history = $chat->messages->map(fn($m) => [
            'role' => $m->role,
            'content' => $m->content,
        ])->toArray();

        // Добавляем текущее сообщение
        $history[] = [
            'role' => 'user',
            'content' => $question,
        ];

        // Запрос к GPT
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => array_merge(
                [
                    [
                        'role' => 'system',
                        'content' =>
                        "Ты — дружелюбный ассистент TeachAI.\n" .
                            "Не используй документы.\n" .
                            "Отвечай кратко и естественно.\n" .
                            "Не придумывай факты о Hero Study.\n" .
                            "Ответь на том же языке, на котором был задан вопрос пользователя ({$originalLang}).\n"
                    ]
                ],
                $history
            ),
        ]);

        $reply = $response->choices[0]->message->content;

        Log::info('SMALL TALK ANSWER:', [$reply]);

        // Сохраняем ответ ассистента
        $chat->messages()->create([
            'role' => 'assistant',
            'content' => $reply,
        ]);

        // Лог
        AiLog::create([
            'user_id' => auth()->id(),
            'chat_id' => $chat->id,
            'question' => $question,
            'matched_titles' => ['SMALL_TALK'],
            'context' => 'SMALL_TALK',
            'final_answer' => $reply,
            'error' => null,
            'duration_ms' => (microtime(true) - LARAVEL_START) * 1000,
        ]);

        return response()->json([
            'answer' => $reply,
        ]);
    }
}
