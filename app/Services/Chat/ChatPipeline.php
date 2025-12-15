<?php

namespace App\Services\Chat;

use App\Models\AiLog;
use App\Models\Chat;
use App\Models\Document;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use App\Services\Chat\Handlers\SmallTalkHandler;
use App\Services\Chat\Handlers\FallbackHandler;
use App\Services\Chat\Handlers\ClarifyHandler;
use App\Services\Chat\Handlers\DocumentAnswerHandler;

class ChatPipeline
{
    public function handle(Chat $chat, string $question)
    {
        $originalQuestion = $question;

        $detected  = $this->translateAndDetect($question);

        $originalLang = $detected['lang'];      // ru / kk / en
        $questionRu   = $detected['ru'];        // ВСЕГДА русский
        $originalText = $detected['original'];  // исходный текст

        Log::info('QUESTION LANG', [$originalLang]);
        Log::info('QUESTION RU', [$questionRu]);

        $titles = app(TitleClassifier::class)
            ->classify($questionRu);

        Log::info('TITLE', [$titles]);

        if ($titles === ['SMALL_TALK']) {
            return app(SmallTalkHandler::class)->handle($chat, $question, $originalLang);
        }

        if ($titles === ['NOT_RELATED']) {
            return app(FallbackHandler::class)->notRelated($chat, $originalLang);
        }

        if ($titles === ['OUT_OF_SCOPE']) {
            return app(FallbackHandler::class)->outOfScope($chat, $originalLang);
        }

        if (empty($titles)) {
            return app(ClarifyHandler::class)->handle($chat, $question, $originalLang);
        }

        return app(DocumentAnswerHandler::class)
            ->handle($chat, $question, $titles, $originalLang);
    }

    protected function translateAndDetect(string $text): array
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' =>
                    "Ты языковой анализатор и переводчик.\n" .
                        "Твоя задача:\n" .
                        "1. Определить язык исходного текста.\n" .
                        "2. Если текст НЕ на русском — перевести его на русский.\n" .
                        "3. Если текст уже на русском — вернуть его без изменений.\n\n" .
                        "Верни СТРОГО JSON следующего формата:\n" .
                        "{\n" .
                        "  \"lang\": \"ru | kk | en\",\n" .
                        "  \"ru\": \"перевод на русский\",\n" .
                        "  \"original\": \"оригинальный текст\"\n" .
                        "}\n\n" .
                        "Без пояснений. Без markdown. Только JSON."
                ],
                [
                    'role' => 'user',
                    'content' => $text
                ]
            ]
        ]);

        $raw = $response->choices[0]->message->content;

        $data = json_decode($raw, true);

        if (!is_array($data) || !isset($data['ru'], $data['lang'])) {
            // fallback — считаем, что это русский
            return [
                'lang' => 'ru',
                'ru' => $text,
                'original' => $text,
            ];
        }

        return $data;
    }
}
