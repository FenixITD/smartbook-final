<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Dto\Chat\MessageDto;
use App\Http\Controllers\Controller;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class OpenConversationController extends Controller
{
    public function __construct(
        private ChatService $chatService,
    ) {
    }

    public function __invoke(int $bookId): JsonResponse
    {
        $result = $this->chatService->openConversation(
            userId: (int) Auth::id(),
            bookId: $bookId,
        );

        return response()->json([
            'conversation_id' => $result->conversationId,
            'messages' => array_map(static fn (MessageDto $message): array => $message->toArray(),
                $result->messages,
            ),
        ]);
    }
}
