<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Dto\Chat\SendMessageDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class SendMessageController extends Controller
{
    public function __construct(
        private ChatService $chatService,
    ) {
    }

    public function __invoke(SendMessageRequest $request, int $conversationId): JsonResponse
    {
        $user = Auth::user();
        assert($user !== null);

        $dto = SendMessageDto::fromRequest($request, $conversationId, $user);

        $messageDto = $this->chatService->sendMessage($dto);

        return response()->json($messageDto->toArray(), 201);
    }
}
