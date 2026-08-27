<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Dto\Chat\ConversationSummaryDto;
use App\Dto\Chat\MessageDto;
use App\Models\Book;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Repositories\Eloquent\ConversationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class ConversationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ConversationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $activityLogger = Mockery::mock(\Spatie\Activitylog\ActivityLogger::class);
        $activityLogger->shouldReceive('useLog')->andReturnSelf();
        $activityLogger->shouldReceive('event')->andReturnSelf();
        $activityLogger->shouldReceive('performedOn')->andReturnSelf();
        $activityLogger->shouldReceive('withProperties')->andReturnSelf();
        $activityLogger->shouldReceive('log')->andReturnNull();
        $this->app->singleton(\Spatie\Activitylog\ActivityLogger::class, fn () => $activityLogger);

        $this->repository = new ConversationRepository();
    }

    private function createAuthor(): int
    {
        return DB::table('authors')->insertGetId(['name' => 'Test Author', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function createBook(): Book
    {
        $authorId = $this->createAuthor();

        return Book::create([
            'title' => 'Test Book',
            'slug' => 'test-book-' . uniqid(),
            'author_id' => $authorId,
            'description' => 'Description',
            'price' => 9.99,
            'stock' => 10,
            'status' => 'active',
        ]);
    }

    private function createUser(): User
    {
        return User::factory()->create();
    }

    private function createConversation(User $user, Book $book, string $status = 'open'): Conversation
    {
        return Conversation::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => $status,
        ]);
    }

    private function createMessage(Conversation $conversation, User $user, array $attributes = []): Message
    {
        return Message::create(array_merge([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'body' => 'Test message',
            'read_at' => null,
        ], $attributes));
    }

    public function test_get_all_with_unread_counts_returns_empty_array_when_no_conversations(): void
    {
        $result = $this->repository->getAllWithUnreadCounts();

        $this->assertSame([], $result);
    }

    public function test_get_all_with_unread_counts_excludes_conversations_without_messages(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $this->createConversation($user, $book);

        $result = $this->repository->getAllWithUnreadCounts();

        $this->assertSame([], $result);
    }

    public function test_get_all_with_unread_counts_returns_dto_for_conversation_with_messages(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);
        $this->createMessage($conversation, $user);

        $result = $this->repository->getAllWithUnreadCounts();

        $this->assertCount(1, $result);
        $this->assertInstanceOf(ConversationSummaryDto::class, $result[0]);
    }

    public function test_get_all_with_unread_counts_dto_contains_correct_data(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);
        $this->createMessage($conversation, $user, ['body' => 'Hello there']);

        $result = $this->repository->getAllWithUnreadCounts();
        $dto = $result[0];

        $this->assertSame($conversation->id, $dto->id);
        $this->assertSame($user->id, $dto->userId);
        $this->assertSame($user->name, $dto->userName);
        $this->assertNull($dto->userEmail);
        $this->assertSame($book->id, $dto->bookId);
        $this->assertSame($book->title, $dto->bookTitle);
        $this->assertSame('open', $dto->status);
        $this->assertSame('Hello there', $dto->lastMessageBody);
    }

    public function test_get_all_with_unread_counts_counts_only_unread_messages_from_conversation_owner(): void
    {
        $user = $this->createUser();
        $admin = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);

        $this->createMessage($conversation, $user, ['read_at' => null]);
        $this->createMessage($conversation, $user, ['read_at' => null]);
        $this->createMessage($conversation, $admin, ['read_at' => null]);
        $this->createMessage($conversation, $user, ['read_at' => now()]);

        $result = $this->repository->getAllWithUnreadCounts();

        $this->assertSame(2, $result[0]->unreadCount);
    }

    public function test_get_all_with_unread_counts_returns_zero_unread_when_all_messages_are_read(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);
        $this->createMessage($conversation, $user, ['read_at' => now()]);

        $result = $this->repository->getAllWithUnreadCounts();

        $this->assertSame(0, $result[0]->unreadCount);
    }

    public function test_get_all_with_unread_counts_orders_by_updated_at_descending(): void
    {
        $user = $this->createUser();
        $book1 = $this->createBook();
        $book2 = $this->createBook();

        $older = $this->createConversation($user, $book1);
        $this->createMessage($older, $user);

        $newer = $this->createConversation($user, $book2);
        $this->createMessage($newer, $user);

        DB::table('conversations')->where('id', $older->id)->update(['updated_at' => now()->subHour()]);
        DB::table('conversations')->where('id', $newer->id)->update(['updated_at' => now()]);

        $result = $this->repository->getAllWithUnreadCounts();

        $this->assertSame($newer->id, $result[0]->id);
        $this->assertSame($older->id, $result[1]->id);
    }

    public function test_get_owner_id_returns_user_id_for_existing_conversation(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);

        $result = $this->repository->getOwnerId($conversation->id);

        $this->assertSame($user->id, $result);
    }

    public function test_get_owner_id_returns_null_for_nonexistent_conversation(): void
    {
        $result = $this->repository->getOwnerId(99999);

        $this->assertNull($result);
    }

    public function test_find_or_create_by_user_and_book_creates_new_conversation(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();

        $id = $this->repository->findOrCreateByUserAndBook($user->id, $book->id);

        $this->assertDatabaseHas('conversations', [
            'id' => $id,
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'open',
        ]);
    }

    public function test_find_or_create_by_user_and_book_returns_existing_conversation(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);

        $id = $this->repository->findOrCreateByUserAndBook($user->id, $book->id);

        $this->assertSame($conversation->id, $id);
        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_get_messages_returns_empty_array_when_no_messages(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);

        $result = $this->repository->getMessages($conversation->id);

        $this->assertSame([], $result);
    }

    public function test_get_messages_returns_message_dtos(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);
        $this->createMessage($conversation, $user, ['body' => 'First']);

        $result = $this->repository->getMessages($conversation->id);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(MessageDto::class, $result[0]);
        $this->assertSame('First', $result[0]->body);
        $this->assertSame($user->id, $result[0]->userId);
        $this->assertSame($user->name, $result[0]->senderName);
        $this->assertSame($conversation->id, $result[0]->conversationId);
    }

    public function test_get_messages_returns_messages_ordered_oldest_first(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);

        $first = $this->createMessage($conversation, $user, ['body' => 'First', 'created_at' => now()->subMinutes(5)]);
        $second = $this->createMessage($conversation, $user, ['body' => 'Second', 'created_at' => now()]);

        $result = $this->repository->getMessages($conversation->id);

        $this->assertSame($first->id, $result[0]->id);
        $this->assertSame($second->id, $result[1]->id);
    }

    public function test_get_messages_only_returns_messages_for_given_conversation(): void
    {
        $user = $this->createUser();
        $book1 = $this->createBook();
        $book2 = $this->createBook();
        $conversation1 = $this->createConversation($user, $book1);
        $conversation2 = $this->createConversation($user, $book2);

        $this->createMessage($conversation1, $user, ['body' => 'Belongs to first']);
        $this->createMessage($conversation2, $user, ['body' => 'Belongs to second']);

        $result = $this->repository->getMessages($conversation1->id);

        $this->assertCount(1, $result);
        $this->assertSame('Belongs to first', $result[0]->body);
    }

    public function test_get_total_unread_count_returns_zero_when_no_messages(): void
    {
        $result = $this->repository->getTotalUnreadCount();

        $this->assertSame(0, $result);
    }

    public function test_get_total_unread_count_counts_unread_messages_from_conversation_owners(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);

        $this->createMessage($conversation, $user, ['read_at' => null]);
        $this->createMessage($conversation, $user, ['read_at' => null]);

        $result = $this->repository->getTotalUnreadCount();

        $this->assertSame(2, $result);
    }

    public function test_get_total_unread_count_excludes_read_messages(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);

        $this->createMessage($conversation, $user, ['read_at' => now()]);
        $this->createMessage($conversation, $user, ['read_at' => null]);

        $result = $this->repository->getTotalUnreadCount();

        $this->assertSame(1, $result);
    }

    public function test_get_total_unread_count_excludes_messages_not_from_conversation_owner(): void
    {
        $user = $this->createUser();
        $admin = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book);

        $this->createMessage($conversation, $admin, ['read_at' => null]);

        $result = $this->repository->getTotalUnreadCount();

        $this->assertSame(0, $result);
    }

    public function test_get_total_unread_count_sums_across_multiple_conversations(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $book1 = $this->createBook();
        $book2 = $this->createBook();

        $conversation1 = $this->createConversation($user1, $book1);
        $conversation2 = $this->createConversation($user2, $book2);

        $this->createMessage($conversation1, $user1, ['read_at' => null]);
        $this->createMessage($conversation2, $user2, ['read_at' => null]);

        $result = $this->repository->getTotalUnreadCount();

        $this->assertSame(2, $result);
    }

    public function test_close_sets_status_to_closed(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $conversation = $this->createConversation($user, $book, 'open');

        $this->repository->close($conversation->id);

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'closed',
        ]);
    }

    public function test_close_does_not_affect_other_conversations(): void
    {
        $user = $this->createUser();
        $book1 = $this->createBook();
        $book2 = $this->createBook();

        $target = $this->createConversation($user, $book1, 'open');
        $other = $this->createConversation($user, $book2, 'open');

        $this->repository->close($target->id);

        $this->assertDatabaseHas('conversations', [
            'id' => $other->id,
            'status' => 'open',
        ]);
    }
}
