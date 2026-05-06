<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

// Этот контроллер вызывается, когда пользователь открывает чат на странице книги.
// Он ищет существующий диалог или создаёт новый (firstOrCreate).
final class OpenConversationController extends Controller
{
    public function __invoke(int $book): JsonResponse
    {
        // firstOrCreate(условие поиска, данные для создания если не найдено)
        // Если диалог уже есть — вернём его. Нет — создадим новый.
        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'book_id' => $book,
            ],
            [
                'status' => 'open',
            ]
        );

        // Загружаем сообщения вместе с отправителем (eager loading).
        // Без with('messages.user') Laravel сделал бы N+1 запросов.
        $conversation->load('messages.user');

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $conversation->messages->map(static fn ($msg) => [
                'id' => $msg->id,
                'body' => $msg->body,
                'user_id' => $msg->user_id,
                'sender_name' => $msg->user->name,
                'created_at' => $msg->created_at?->toIso8601String(),
            ]),
        ]);
    }
}
