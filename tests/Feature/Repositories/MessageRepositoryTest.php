<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Dto\Chat\MessageDto;
use App\Models\Author;
use App\Models\Book;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Repositories\Eloquent\MessageRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MessageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MessageRepository();
    }

    private function makeConversation(): Conversation
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $user = User::factory()->create();

        return Conversation::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'open',
        ]);
    }

    public function test_create_returns_message_dto(): void
    {
        $conversation = $this->makeConversation();

        $result = $this->repository->create($conversation->id, $conversation->user_id, 'Hello');

        $this->assertInstanceOf(MessageDto::class, $result);
    }

    public function test_create_persists_message_to_database(): void
    {
        $conversation = $this->makeConversation();

        $this->repository->create($conversation->id, $conversation->user_id, 'Hello');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $conversation->user_id,
            'body' => 'Hello',
        ]);
    }

    public function test_create_returns_dto_with_correct_fields(): void
    {
        $conversation = $this->makeConversation();

        $result = $this->repository->create($conversation->id, $conversation->user_id, 'Test body');

        $this->assertSame($conversation->id, $result->conversationId);
        $this->assertSame($conversation->user_id, $result->userId);
        $this->assertSame('Test body', $result->body);
        $this->assertNotNull($result->id);
        $this->assertNotNull($result->senderName);
        $this->assertNotNull($result->createdAt);
    }

    public function test_create_dto_sender_name_matches_user(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $user = User::factory()->create(['name' => 'John Doe']);

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'open',
        ]);

        $result = $this->repository->create($conversation->id, $user->id, 'Hi');

        $this->assertSame('John Doe', $result->senderName);
    }

    public function test_mark_user_messages_as_read_updates_read_at(): void
    {
        $conversation = $this->makeConversation();

        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $conversation->user_id,
            'body' => 'Unread message',
            'read_at' => null,
        ]);

        $this->repository->markUserMessagesAsRead($conversation->id);

        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $conversation->user_id,
            'read_at' => null,
        ]);
    }

    public function test_mark_user_messages_as_read_does_not_affect_already_read_messages(): void
    {
        $conversation = $this->makeConversation();
        $readAt = now()->subHour()->toDateTimeString();

        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $conversation->user_id,
            'body' => 'Already read',
            'read_at' => $readAt,
        ]);

        $this->repository->markUserMessagesAsRead($conversation->id);

        $message = Message::where('conversation_id', $conversation->id)->first();
        $this->assertNotNull($message->read_at);
        $this->assertEqualsWithDelta(
            strtotime($readAt),
            $message->read_at->timestamp,
            2
        );
    }

    public function test_mark_user_messages_as_read_only_marks_conversation_owner_messages(): void
    {
        $conversation = $this->makeConversation();

        $otherUser = User::factory()->create();

        $ownerMessage = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $conversation->user_id,
            'body' => 'Owner message',
            'read_at' => null,
        ]);

        $otherMessage = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $otherUser->id,
            'body' => 'Other user message',
            'read_at' => null,
        ]);

        $this->repository->markUserMessagesAsRead($conversation->id);

        $this->assertNotNull($ownerMessage->fresh()->read_at);
        $this->assertNull($otherMessage->fresh()->read_at);
    }

    public function test_mark_user_messages_as_read_does_not_affect_other_conversations(): void
    {
        $conversation = $this->makeConversation();

        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $otherUser = User::factory()->create();
        $otherConversation = Conversation::create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'status' => 'open',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $conversation->user_id,
            'body' => 'Message in target conversation',
            'read_at' => null,
        ]);

        $otherMessage = Message::create([
            'conversation_id' => $otherConversation->id,
            'user_id' => $otherUser->id,
            'body' => 'Message in other conversation',
            'read_at' => null,
        ]);

        $this->repository->markUserMessagesAsRead($conversation->id);

        $this->assertNull($otherMessage->fresh()->read_at);
    }
}
