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
                    "Ты ассистент системы Hero Study.\n\n" .
                        "У тебя есть список всех разделов документа Hero Study.\n" .
                        "Задача: выбрать только те разделы (titles), которые наиболее подходят под вопрос пользователя.\n\n" .

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

                        "ПРАВИЛЬНЫЕ ПРИМЕРЫ:\n" .
                        "[\"1.1 Как войти в систему - Hero Study\"]\n" .
                        "[\"1.1 Как войти в систему - Hero Study\", \"1.2 Навигация: основные рабочие блоки - Hero Study\"]\n\n" .

                        "НЕПРАВИЛЬНЫЕ ПРИМЕРЫ:\n" .
                        "{ \"title\": \"...\" }\n" .
                        "```json [ ... ] ```\n" .
                        "[{\"title\": \"...\"}]\n\n" .

                        "Если подходящих разделов нет — верни пустой массив: []\n"
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
            $contextText = "Нет подходящих разделов. Ответь: 'Информация не найдена в документации Hero Study.'";
        }


        // ============================================================
        // ШАГ 4: Финальный GPT ответ
        // ============================================================

        Log::info('GPT FINAL ANSWER REQUEST START');

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' =>
                    "Ты ассистент Hero Study. Используй ТОЛЬКО этот контекст:\n\n"
                        . $contextText
                ],
                [
                    'role' => 'user',
                    'content' => $question
                ]
            ]
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
