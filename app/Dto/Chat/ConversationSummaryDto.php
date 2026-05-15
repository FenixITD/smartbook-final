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
            userEmail: $user->email,
            bookId: $conversation->book_id,
            bookTitle: $book->title,
            status: $conversation->status,
            lastMessageBody: $conversation->messages->first()?->body,
            updatedAt: $conversation->updated_at?->format('d.m.Y H:i') ?? '',
            unreadCount: $unreadCount,
        );
    }

    public function __construct(
        public int $id,
        public int $userId,
        public string $userName,
        public string|null $userEmail,
        public int $bookId,
        public string $bookTitle,
        public string $status,
        public string|null $lastMessageBody,
        public string $updatedAt,
        public int $unreadCount,
    ) {
    }
}
