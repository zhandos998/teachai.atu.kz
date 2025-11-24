<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class ChatController extends Controller
{

    public function send(Request $request)
    {
        $chat = Chat::findOrFail($request->chat_id);

        if ($chat->user_id !== auth()->id()) {
            abort(403, 'Access denied.');
        }

        // 1) Сохраняем сообщение пользователя
        $userMessage = $chat->messages()->create([
            'role' => 'user',
            'content' => $request->message,
        ]);

        // 2) Вызов OpenAI
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => $chat->messages->map(fn($m) => [
                'role' => $m->role,
                'content' => $m->content
            ])->push([
                'role' => 'user',
                'content' => $request->message
            ]),
        ]);

        $answer = $response->choices[0]->message->content;

        // 3) Сохраняем ответ ассистента
        $assistantMessage = $chat->messages()->create([
            'role' => 'assistant',
            'content' => $answer,
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
