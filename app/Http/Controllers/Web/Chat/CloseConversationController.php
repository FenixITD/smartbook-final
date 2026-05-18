<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;

final class CloseConversationController extends Controller
{
    public function __construct(
        private ChatService $chatService,
    ) {}

    public function __invoke(int $conversationId): JsonResponse
    {
        $this->chatService->closeConversation($conversationId);

        return response()->json(['status' => 'closed']);
    }
}
