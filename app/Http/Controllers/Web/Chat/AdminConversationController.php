<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use Illuminate\View\View;

final class AdminConversationController extends Controller
{
    public function __construct(
        private MessageRepositoryInterface $messageRepository,
    ) {
    }

    public function __invoke(): View
    {
        $conversations = Conversation::with(['user', 'book', 'messages' => static function ($q): void {
            $q->latest()->limit(1);
        }])
            ->orderByDesc('updated_at')
            ->get();

        $unreadCounts = $conversations->mapWithKeys(
            fn ($c) => [$c->id => $this->messageRepository->countUnread($c->id)]
        );

        return view('chat.admin', compact('conversations', 'unreadCounts'));
    }
}
