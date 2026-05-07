<?php

declare(strict_types=1);

namespace App\Dto\Chat;

use App\Models\Book;
use App\Models\Conversation;
use App\Models\User;

final readonly class ConversationSummaryDto
{
    public static function fromModel(Conversation $conversation): self
    {
        /** @var int $unreadCount */
        $unreadCount = $conversation->getAttribute('unread_count');

        /** @var User $user */
        $user = $conversation->user;

        /** @var Book $book */
        $book = $conversation->book;

        return new self(
            id: $conversation->id,
            userId: $conversation->user_id,
            userName: $user->name,
            bookId: $conversation->book_id,
            bookTitle: $book->title,
            lastMessageBody: $conversation->messages->first()?->body,
            updatedAt: $conversation->updated_at?->toIso8601String() ?? '',
            unreadCount: $unreadCount,
        );
    }

    public function __construct(
        public int $id,
        public int $userId,
        public string $userName,
        public int $bookId,
        public string $bookTitle,
        public string|null $lastMessageBody,
        public string $updatedAt,
        public int $unreadCount,
    ) {
    }
}
