<?php

namespace App\Services\Chat\Handlers;

use App\Models\Chat;
use App\Models\AiLog;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class ClarifyHandler
{
    public function handle(Chat $chat, string $question, string $originalLang)
    {
        // Запрос к GPT — определяем возможные смыслы вопроса
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' =>
                    "Ты ассистент TeachAI.\n" .
                        "Ответь на том же языке, на котором был задан вопрос пользователя ({$originalLang}).\n" .
                        "Если вопрос пользователя неоднозначный и может относиться к разным разделам,\n" .
                        "твоя задача определить, какие варианты он может иметь в виду.\n\n" .
                        "Верни строго JSON массив строк.\n\n" .
                        "Пример:\n" .
                        "Вопрос: \"Как загрузить предметы в Hero Study?\"\n" .
                        "Ответ: [\"Загрузка УМКД\", \"Загрузка дисциплин\", \"Загрузка образовательных программ\"]\n\n" .
                        "Если вопрос вообще не относится к Hero Study — верни []"
                ],
                [
                    'role' => 'user',
                    'content' => $question
                ]
            ]
        ]);

        $raw = $response->choices[0]->message->content;

        Log::info('CLARIFY GPT RAW:', [$raw]);

        $options = json_decode($raw, true);

        // Если GPT не дал вариантов
        if (!is_array($options) || empty($options)) {

            $answer =
                "Я не уверен, что правильно понял ваш вопрос.\n" .
                "Пожалуйста, уточните, что именно вы хотите сделать в TeachAI.";
            if ($originalLang == 'kk') {
                $answer =
                    "Сұрағыңызды дұрыс түсінгеніме сенімді емеспін.\n" .
                    "TeachAI жүйесінде нақты қандай әрекет жасағыңыз келетінін нақтылап өтсеңіз.";
            }
            // if ($originalLang == 'kk') {
            // }
            $chat->messages()->create([
                'role' => 'assistant',
                'content' => $answer,
            ]);

            AiLog::create([
                'user_id' => auth()->id(),
                'chat_id' => $chat->id,
                'question' => $question,
                'matched_titles' => [],
                'context' => $raw,
                'final_answer' => $answer,
                'error' => null,
                'duration_ms' => (microtime(true) - LARAVEL_START) * 1000,
            ]);

            return response()->json([
                'answer' => $answer,
            ]);
        }

        // Формируем список вариантов
        $list = "";
        foreach ($options as $opt) {
            $list .= "- {$opt}\n";
        }

        $answer =
            "Уточните, пожалуйста, что вы имели в виду?\n\n" .
            $list;
        if ($originalLang == 'kk') {
            $answer =
                "Нақты нені меңзегеніңізді нақтылап өтсеңіз.\n\n" .
                $list;
        }

        // Сохраняем сообщение
        $chat->messages()->create([
            'role' => 'assistant',
            'content' => $answer,
        ]);

        AiLog::create([
            'user_id' => auth()->id(),
            'chat_id' => $chat->id,
            'question' => $question,
            'matched_titles' => [],
            'context' => $raw,
            'final_answer' => $answer,
            'error' => null,
            'duration_ms' => (microtime(true) - LARAVEL_START) * 1000,
        ]);

        return response()->json([
            'answer' => $answer,
        ]);
    }
}
