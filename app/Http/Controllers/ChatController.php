<?php

namespace App\Http\Controllers;

use App\Models\AiLog;
use App\Models\Chat;
use App\Models\Document;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{

    public function send(Request $request)
    {
        $chat = Chat::findOrFail($request->chat_id);

        if ($chat->user_id !== auth()->id()) {
            abort(403, 'Access denied.');
        }

        $question = $request->message;

        // 1) Сохраняем сообщение пользователя
        $chat->messages()->create([
            'role' => 'user',
            'content' => $question,
        ]);

        Log::info('USER QUESTION:', [$question]);


        // ============================================================
        // ШАГ 1: Получаем список titles
        // ============================================================

        $allTitles = Document::pluck('title')->toArray();

        Log::info('ALL TITLES COUNT:', [count($allTitles)]);
        Log::info('ALL TITLES SAMPLE:', array_slice($allTitles, 0, 5)); // первые 5 штук


        // Формируем список для GPT
        $titlesString = implode("\n", array_map(fn($t) => "- " . $t, $allTitles));


        // ============================================================
        // ШАГ 2: GPT выбирает подходящие Titles
        // ============================================================

        Log::info('GPT TITLE CLASSIFICATION REQUEST START');

        $classification = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' =>
                    "Ты ассистент системы TeachAI.\n\n" .

                        "ВАЖНО:\n" .
                        "Если вопрос НЕ относится к документам, не содержит терминов Hero Study, УМКД, учебного процесса,\n" .
                        "расписания, дисциплин, турникета, личного кабинета и т.п.,\n" .
                        "а является обычным бытовым сообщением (например: \"привет\", \"как дела\", \"спасибо\",\n" .
                        "\"кто ты\", \"что умеешь\", \"пока\", \"ок\", \"ясно\"),\n" .
                        "то НЕ выполняй классификацию titles.\n" .
                        "Вместо этого верни JSON: [\"SMALL_TALK\"]\n\n" .
                        "Если вопрос касается людей, отношений, общения, эмоций,\n" .
                        "поведения, личных тем, советов, психологии,\n" .
                        "или любых вопросов НЕ относящихся к документам, УМКД, Hero Study,\n" .
                        "верни JSON: [\"NOT_RELATED\"].\n" .

                        "Твоя основная задача — найти подходящие titles к вопросу пользователя.\n" .
                        "Если вопрос пользователя НЕ относится к разделам из списка — верни пустой массив [].\n" .
                        "Ты НЕ ДОЛЖЕН угадывать. Ты НЕ ДОЛЖЕН придумывать.\n" .
                        "Если есть сомнения — верни [].\n\n" .

                        "СПИСОК РАЗДЕЛОВ:\n" .
                        $titlesString . "\n\n" .

                        "ТВОЯ ЗАДАЧА:\n" .
                        "- проанализировать вопрос пользователя\n" .
                        "- сравнить его со всеми titles\n" .
                        "- выбрать 1–5 наиболее подходящих titles\n\n" .

                        "ФОРМАТ ОТВЕТА (ОЧЕНЬ ВАЖНО):\n" .
                        "- Верни строго JSON МАССИВ строк.\n" .
                        "- Только массив строк.\n" .
                        "- Без объектов.\n" .
                        "- Без ключей.\n" .
                        "- Без дополнительных слов.\n" .
                        "- Без ```json блока.\n\n" .

                        "ПРИМЕРЫ ПРАВИЛЬНО:\n" .
                        "[\"1.1 Как войти в систему - Hero Study\"]\n" .
                        "[\"1.1 Как войти в систему - Hero Study\", \"1.2 Навигация: основные рабочие блоки - Hero Study\"]\n\n" .

                        "ЕСЛИ ПОДХОДЯЩИХ НЕТ — верни пустой массив []\n"

                ],
                [
                    'role' => 'user',
                    'content' => $question
                ]
            ]
        ]);

        $jsonTitles = $classification->choices[0]->message->content;

        Log::info('GPT TITLE CLASSIFICATION RESPONSE RAW:', [$jsonTitles]);


        // Парсим JSON
        $matchedTitles = json_decode($jsonTitles, true);

        if (!is_array($matchedTitles)) {
            AiLog::create([
                'user_id' => auth()->id(),
                'chat_id' => $chat->id,
                'question' => $question,
                'matched_titles' => [],
                'error' => 'JSON_PARSE_ERROR: ' . $jsonTitles,
            ]);
            Log::error('FAILED TO PARSE JSON FROM GPT:', [$jsonTitles]);
            $matchedTitles = [];
        }

        Log::info('MATCHED TITLES:', $matchedTitles);

        // ===============================================
        // SMALL TALK через GPT (если GPT вернул специальный маркер ["SMALL_TALK"])
        // ===============================================
        if ($matchedTitles === ["SMALL_TALK"]) {

            // Формируем историю диалога
            $history = $chat->messages->map(fn($m) => [
                'role' => $m->role,
                'content' => $m->content
            ])->toArray();

            // Добавляем текущее сообщение
            $history[] = [
                'role' => 'user',
                'content' => $question
            ];

            // Отправляем в GPT как обычный чат
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => array_merge(
                    [
                        [
                            'role' => 'system',
                            'content' =>
                            "Ты — дружелюбный ассистент TeachAI.\n" .
                                "Не используй документы.\n" .
                                "Просто веди диалог, отвечай естественно.\n" .
                                "Не придумывай факты о Hero Study.\n"
                        ]
                    ],
                    $history
                )
            ]);

            $reply = $response->choices[0]->message->content;

            // Сохраняем в БД
            $chat->messages()->create([
                'role' => 'assistant',
                'content' => $reply,
            ]);

            AiLog::create([
                'user_id' => auth()->id(),
                'chat_id' => $chat->id,
                'question' => $question,
                'matched_titles' => $matchedTitles,
                'context' =>                             "Ты — дружелюбный ассистент TeachAI.\n" .
                    "Не используй документы.\n" .
                    "Просто веди диалог, отвечай естественно.\n" .
                    "Не придумывай факты о Hero Study.\n",
                'final_answer' => $reply,
                'error' => null,
                'duration_ms' => (microtime(true) - LARAVEL_START) * 1000,
            ]);

            return response()->json([
                'answer' => $reply
            ]);
        }

        // ===============================================
        // Вопрос не относится к Hero Study (NOT_RELATED)
        // ===============================================
        if ($matchedTitles === ["NOT_RELATED"]) {

            $fallback =
                "Информация не найдена.\n\n" .
                "Если вам нужен точный ответ, обратитесь в Учебно-методическое управление:\n" .
                "- вн.т.: 195\n" .
                "- n.ahmetova@atu.edu.kz\n" .
                "- nursulu.akhmetova.2013@mail.ru\n" .
                "- каб. 521\n";

            $chat->messages()->create([
                'role' => 'assistant',
                'content' => $fallback,
            ]);

            return response()->json([
                'answer' => $fallback
            ]);
        }
        // ===============================================
        // SHAG 2.5 — Уточняем вопрос если GPT не нашел titles
        // ===============================================
        if (empty($matchedTitles)) {

            // Просим GPT определить 2–4 возможные смыслы вопроса
            $clarifyResponse = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' =>
                        "Ты ассистент TeachAI.\n" .
                            "Если вопрос пользователя неоднозначный и может относиться к разным разделам\n" .
                            "— твоя задача определить, какие конкретно варианты он может иметь в виду.\n\n" .

                            "Верни строго JSON массив вариантов.\n" .
                            "Примеры:\n" .
                            "Вопрос: \"Как загрузить предметы в Hero Study?\"\n" .
                            "Ответ: [\"Загрузка УМКД\", \"Загрузка дисциплин\", \"Загрузка образовательных программ\"]\n\n" .

                            "Если вариантов мало — верни минимум 2.\n" .
                            "Если вопрос вообще не относится к Hero Study — верни []"
                    ],
                    [
                        'role' => 'user',
                        'content' => $question
                    ]
                ]
            ]);

            $possibleOptions = json_decode($clarifyResponse->choices[0]->message->content, true);

            // Если GPT вообще не понял — просто ответим, что вопрос непонятен
            if (!is_array($possibleOptions) || empty($possibleOptions)) {

                $fallback = "Я не уверен, что правильно понял ваш вопрос. " .
                    "Пожалуйста, уточните, что именно вы хотите сделать в Hero Study.";

                $chat->messages()->create([
                    'role' => 'assistant',
                    'content' => $fallback,
                ]);

                AiLog::create([
                    'user_id' => auth()->id(),
                    'chat_id' => $chat->id,
                    'question' => $question,
                    'matched_titles' => $matchedTitles,
                    'context' => $clarifyResponse->choices[0]->message->content,
                    'final_answer' => $fallback,
                    'error' => null,
                    'duration_ms' => (microtime(true) - LARAVEL_START) * 1000,
                ]);

                return response()->json([
                    'answer' => $fallback
                ]);
            }

            // Формируем красивый уточняющий вопрос на основе вариантов
            $list = "";
            foreach ($possibleOptions as $opt) {
                $list .= "- {$opt}\n";
            }

            $clarify = "Уточните, пожалуйста, что вы имели в виду?\n\n" . $list;

            // Сохраняем и отправляем пользователю
            $chat->messages()->create([
                'role' => 'assistant',
                'content' => $clarify,
            ]);

            AiLog::create([
                'user_id' => auth()->id(),
                'chat_id' => $chat->id,
                'question' => $question,
                'matched_titles' => $matchedTitles,
                'context' => $clarifyResponse->choices[0]->message->content,
                'final_answer' => $clarify,
                'error' => null,
                'duration_ms' => (microtime(true) - LARAVEL_START) * 1000,
            ]);

            return response()->json([
                'answer' => $clarify
            ]);
        }

        // ============================================================
        // ШАГ 3: Загружаем документы из БД
        // ============================================================

        $docs = Document::whereIn('title', $matchedTitles)->get();

        Log::info('MATCHED DOCS COUNT:', [$docs->count()]);


        $contextText = "";
        foreach ($docs as $doc) {
            $contextText .= "### {$doc->title}\n{$doc->text}\n\n";
        }

        Log::info('CONTEXT USED FOR GPT:', [$contextText]);


        if ($contextText === "") {
            $contextText = "Нет подходящих разделов.\n Ответь: Информация не найдена.\n" .
                "Если вам нужен точный ответ, обратитесь в Учебно-методическое управление:\n" .
                "- вн.т.: 195\n" .
                "- n.ahmetova@atu.edu.kz\n" .
                "- nursulu.akhmetova.2013@mail.ru\n" .
                "- каб. 521\n";
        }


        // ============================================================
        // ШАГ 4: Финальный GPT ответ
        // ============================================================

        Log::info('GPT FINAL ANSWER REQUEST START');

        $history = $chat->messages->map(fn($m) => [
            'role' => $m->role,
            'content' => $m->content
        ])->toArray();

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => array_merge(
                [
                    [
                        'role' => 'system',
                        'content' => "Ты ассистент TeachAI. Используй ТОЛЬКО этот контекст:\n\n" . $contextText
                    ]
                ],
                $history, // 🔥 вся история чата
                [
                    [
                        'role' => 'user',
                        'content' => $question
                    ]
                ]
            )
        ]);

        $answer = $response->choices[0]->message->content;

        Log::info('GPT FINAL ANSWER:', [$answer]);


        // 5) Сохраняем ответ ассистента
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
            'answer' => $answer
        ]);
    }



    public function createChat()
    {
        $chat = Chat::create([
            'user_id' => auth()->id(),
            'title' => 'Новый чат'
        ]);

        return response()->json(['chat' => $chat]);
    }

    public function loadChat(Chat $chat)
    {
        if ($chat->user_id !== auth()->id()) {
            abort(403, 'Access denied.');
        }

        $chat->load('messages');


        return inertia('Dashboard', [
            'chat' => $chat,
            'messages' => $chat->messages,
        ]);

        // return response()->json([
        //     'chat' => $chat,
        //     'messages' => $chat->messages
        // ]);
    }

    public function listChats()
    {
        $chats = Chat::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'chats' => $chats
        ]);
    }

    public function delete(Chat $chat)
    {
        if ($chat->user_id !== auth()->id()) {
            abort(403, 'Access denied.');
        }

        // Удаляем все сообщения
        $chat->messages()->delete();

        // Удаляем чат
        $chat->delete();

        return response()->json(['success' => true]);
    }
}
