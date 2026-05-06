<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Этот контроллер обрабатывает отправку сообщения.
// Его вызывают и покупатель, и администратор (одним маршрутом).
final class SendMessageController extends Controller
{
    public function __invoke(Request $request, Conversation $conversation): JsonResponse
    {
        // validate() — проверяем входные данные.
        // Если проверка не пройдёт, Laravel автоматически вернёт 422 с ошибками.
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        // Проверяем права:
        // - Покупатель может писать только в свой диалог
        // - Администратор (role === 'admin') может писать в любой
        $user = Auth::user();

        if ($user->role !== 'admin' && $conversation->user_id !== $user->id) {
            abort(403, 'Access denied.');
        }

        // Создаём сообщение
        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        // Загружаем отправителя, чтобы передать его в событие
        $message->load('user');

        // Выстреливаем событие — Reverb перехватит его и отправит
        // всем подписчикам канала conversation.{id} через WebSocket
        MessageSent::dispatch($message);

        return response()->json([
            'id' => $message->id,
            'body' => $message->body,
            'user_id' => $message->user_id,
            'sender_name' => $message->user->name,
            'created_at' => $message->created_at?->toIso8601String(),
        ], 201);
    }
}
