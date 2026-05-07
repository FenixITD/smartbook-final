<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatService;
use Illuminate\View\View;

final class AdminConversationController extends Controller
{
    public function __construct(
        private ChatService $chatService,
    ) {
    }

    public function __invoke(): View
    {
        $conversations = $this->chatService->getAdminConversations();

        return view('chat.admin', compact('conversations'));
    }
}
