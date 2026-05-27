<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\ConversationRepository;
use Illuminate\Http\JsonResponse;

final class CloseConversationController extends Controller
{
    public function __construct(
        private ConversationRepository $repository,
    ) {}

    public function __invoke(int $conversationId): JsonResponse
    {
        $this->repository->close($conversationId);

        return response()->json(['status' => 'closed']);
    }
}
