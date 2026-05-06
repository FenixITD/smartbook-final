<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

// Отдаёт историю сообщений конкретного диалога в JSON.
// Используется модальным окном администратора при открытии диалога.
final class GetMessageController extends Controller
{
    public function __invoke(Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        // Проверяем права так же, как в SendMessageController
        if ($user->role !== 'admin' && $conversation->user_id !== $user->id) {
            abort(403);
        }

        $conversation->load('messages.user');

        return response()->json([
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
