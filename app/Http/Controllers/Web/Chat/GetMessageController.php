<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Dto\Chat\GetConversationMessagesDto;
use App\Dto\Chat\MessageDto;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Chat\GetConversationMessagesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class GetMessageController extends Controller
{
    public function __construct(
        private GetConversationMessagesService $chatService,
    ) {
    }

    public function __invoke(int $conversationId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user instanceof User) {
            abort(401);
        }

        $dto = GetConversationMessagesDto::fromUser($conversationId, $user);

        $messages = $this->chatService->getConversationMessages($dto);

        return response()->json([
            'messages' => array_map(static fn (MessageDto $msg): array => $msg->toArray(),
                $messages,
            ),
        ]);
    }
}
