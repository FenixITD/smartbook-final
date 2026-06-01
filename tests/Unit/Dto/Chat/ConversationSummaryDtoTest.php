<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Chat;

use App\Dto\Chat\ConversationSummaryDto;
use App\Models\Book;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class ConversationSummaryDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data(): void
    {
        $user = new User();
        $user->name = 'Alice';
        $user->email = 'alice@example.com';

        $book = new Book();
        $book->title = 'Dune';

        $message = new Message();
        $message->body = 'Is this still available?';

        $conversation = new Conversation();
        $conversation->id = 15;
        $conversation->user_id = 42;
        $conversation->book_id = 7;
        $conversation->status = 'open';
        $conversation->updated_at = Carbon::parse('2026-06-01 15:30:00');

        $conversation->setAttribute('unread_count', 3);
        $conversation->setRelation('user', $user);
        $conversation->setRelation('book', $book);
        $conversation->setRelation('messages', collect([$message]));

        $dto = ConversationSummaryDto::fromModel($conversation);

        $this->assertSame(15, $dto->id);
        $this->assertSame(42, $dto->userId);
        $this->assertSame('Alice', $dto->userName);
        $this->assertSame('alice@example.com', $dto->userEmail);
        $this->assertSame(7, $dto->bookId);
        $this->assertSame('Dune', $dto->bookTitle);
        $this->assertSame('open', $dto->status);
        $this->assertSame('Is this still available?', $dto->lastMessageBody);
        $this->assertSame('01.06.2026 15:30', $dto->updatedAt);
        $this->assertSame(3, $dto->unreadCount);
    }

    public function test_from_model_creates_dto_with_nulls_and_empty_messages(): void
    {
        $user = new User();
        $user->name = 'Bob';
        $user->email = null;

        $book = new Book();
        $book->title = '1984';

        $conversation = new Conversation();
        $conversation->id = 20;
        $conversation->user_id = 50;
        $conversation->book_id = 10;
        $conversation->status = 'closed';
        $conversation->updated_at = null;

        $conversation->setAttribute('unread_count', 0);
        $conversation->setRelation('user', $user);
        $conversation->setRelation('book', $book);
        $conversation->setRelation('messages', collect([]));

        $dto = ConversationSummaryDto::fromModel($conversation);

        $this->assertSame(20, $dto->id);
        $this->assertSame(50, $dto->userId);
        $this->assertSame('Bob', $dto->userName);
        $this->assertNull($dto->userEmail);
        $this->assertSame(10, $dto->bookId);
        $this->assertSame('1984', $dto->bookTitle);
        $this->assertSame('closed', $dto->status);
        $this->assertNull($dto->lastMessageBody);
        $this->assertSame('', $dto->updatedAt);
        $this->assertSame(0, $dto->unreadCount);
    }
}
