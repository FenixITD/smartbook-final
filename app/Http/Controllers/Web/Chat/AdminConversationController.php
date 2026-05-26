<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\ConversationRepository;
use App\Services\Chat\GetConversationMessagesService;
use Illuminate\View\View;

final class AdminConversationController extends Controller
{
    public function __construct(
        private ConversationRepository $repository,
    ) {
    }

    public function __invoke(): View
    {
        $conversations = $this->repository->getAllWithUnreadCounts();

        return view('chat.admin', compact('conversations'));
    }
}
