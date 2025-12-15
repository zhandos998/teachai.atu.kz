<?php

namespace App\Services\Chat\Handlers;

use App\Models\Chat;
use App\Models\AiLog;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class FallbackHandler
{
    public function notRelated(Chat $chat, string $originalLang)
    {
        $answer =
            "Информация не найдена.\n\n" .
            "Если вам нужна точная структура методической разработки занятия,\n" .
            "обратитесь в Учебно-методическое управление:\n" .
            "- вн.т.: 195\n" .
            "- n.ahmetova@atu.edu.kz\n" .
            "- nursulu.akhmetova.2013@mail.ru\n" .
            "- каб. 521\n";
        if ($originalLang == 'kk') {
            $answer =
                "Ақпарат табылмады.\n" .
                "Егер сізге сабақтың әдістемелік әзірлемесінің нақты құрылымы қажет болса,\n" .
                "Оқу-әдістемелік басқармаға хабарласыңыз:\n" .
                "- ішкі тел.: 195\n" .
                "- n.ahmetova@atu.edu.kz\n" .
                "- nursulu.akhmetova.2013@mail.ru\n" .
                "- 521 кабинет\n";
        }
        $chat->messages()->create([
            'role' => 'assistant',
            'content' => $answer,
        ]);

        AiLog::create([
            'user_id' => auth()->id(),
            'chat_id' => $chat->id,
            'question' => null,
            'matched_titles' => ['NOT_RELATED'],
            'context' => 'NOT_RELATED',
            'final_answer' => $answer,
            'error' => null,
            'duration_ms' => (microtime(true) - LARAVEL_START) * 1000,
        ]);

        return response()->json([
            'answer' => $answer,
        ]);
    }

    public function outOfScope(Chat $chat, string $originalLang)
    {
        $answer =
            "Данные по вашему запросу отсутствуют в базе документов TeachAI.\n\n" .
            "Если вам нужна точная структура методической разработки занятия,\n" .
            "обратитесь в Учебно-методическое управление:\n" .
            "- вн.т.: 195\n" .
            "- n.ahmetova@atu.edu.kz\n" .
            "- nursulu.akhmetova.2013@mail.ru\n" .
            "- каб. 521\n";
        if ($originalLang == 'kk') {
            $answer =
                "Сіздің сұрауыңыз бойынша TeachAI құжаттар базасында ақпарат табылмады.\n" .
                "Егер сізге сабақтың әдістемелік әзірлемесінің нақты құрылымы қажет болса,\n" .
                "Оқу-әдістемелік басқармаға хабарласыңыз:\n" .
                "- ішкі тел.: 195\n" .
                "- n.ahmetova@atu.edu.kz\n" .
                "- nursulu.akhmetova.2013@mail.ru\n" .
                "- 521 кабинет\n";
        }

        $chat->messages()->create([
            'role' => 'assistant',
            'content' => $answer,
        ]);

        AiLog::create([
            'user_id' => auth()->id(),
            'chat_id' => $chat->id,
            'question' => null,
            'matched_titles' => ['OUT_OF_SCOPE'],
            'context' => 'OUT_OF_SCOPE',
            'final_answer' => $answer,
            'error' => null,
            'duration_ms' => (microtime(true) - LARAVEL_START) * 1000,
        ]);

        return response()->json([
            'answer' => $answer,
        ]);
    }
}
