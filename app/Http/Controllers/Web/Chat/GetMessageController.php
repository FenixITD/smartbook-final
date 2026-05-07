<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Dto\Chat\GetConversationMessagesDto;
use App\Dto\Chat\MessageDto;
use App\Http\Controllers\Controller;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class GetMessageController extends Controller
{
    public function __construct(
        private ChatService $chatService,
    ) {
    }

    public function __invoke(int $conversationId): JsonResponse
    {
        $user = Auth::user();
        assert($user !== null);

        $dto = GetConversationMessagesDto::fromUser($conversationId, $user);

        $messages = $this->chatService->getConversationMessages($dto);

        return response()->json([
            'messages' => array_map(static fn (MessageDto $msg): array => $msg->toArray(),
                $messages,
            ),
        ]);
    }
}
