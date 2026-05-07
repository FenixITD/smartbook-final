<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Chat\ConversationSummaryDto;
use App\Dto\Chat\MessageDto;
use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

final class ConversationRepository implements ConversationRepositoryInterface
{
    public function getAllWithUnreadCounts(): array
    {
        return Conversation::with([
            'user:id,name',
            'book:id,title',
            'messages' => static function (Builder $q): void {
                $q->latest()->limit(1);
            },
        ])
            ->withCount([
                'messages as unread_count' => static function (Builder $q): void {
                    $q->whereNull('read_at')
                        ->whereColumn('messages.user_id', 'conversations.user_id');
                },
            ])
            ->orderByDesc('updated_at')
            ->get()
            ->map(static fn (Conversation $conversation) => ConversationSummaryDto::fromModel($conversation))
            ->all();
    }

    public function getOwnerId(int $conversationId): int|null
    {
        /** @var int|null */
        return Conversation::where('id', $conversationId)->value('user_id');
    }

    public function findOrCreateByUserAndBook(int $userId, int $bookId): int
    {
        $conversation = Conversation::firstOrCreate(
            ['user_id' => $userId, 'book_id' => $bookId],
            ['status' => 'open'],
        );

        return $conversation->id;
    }

    public function getMessages(int $conversationId): array
    {
        return Message::with('user:id,name')
            ->where('conversation_id', $conversationId)
            ->oldest()
            ->get()
            ->map(static fn (Message $message) => MessageDto::fromModel($message))
            ->all();
    }
}
