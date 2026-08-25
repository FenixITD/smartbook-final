<?php declare(strict_types=1);

namespace Tests\Feature\Web\Chat;

use App\Events\MessageSentEvent;
use App\Models\Author;
use App\Models\Book;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class ChatWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_admin_can_view_admin_conversations_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('chat.admin'));

        $response->assertStatus(200)->assertViewIs('chat.admin');
    }

    public function test_user_can_open_conversation(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->actingAs($user)->postJson(route('chat.open', $book->id));

        $response->assertStatus(200)->assertJsonStructure(['conversation_id', 'messages']);
        $this->assertDatabaseHas('conversations', ['user_id' => $user->id, 'book_id' => $book->id, 'status' => 'open']);
    }

    public function test_user_can_send_message(): void
    {
        Event::fake([MessageSentEvent::class]);

        $user = User::factory()->create(['role' => 'user']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $conversation = Conversation::create(['user_id' => $user->id, 'book_id' => $book->id, 'status' => 'open']);

        $response = $this->actingAs($user)->postJson(route('chat.messages.store', $conversation->id), [
            'body' => 'Hello from user!'
        ]);

        $response->assertStatus(201)->assertJsonPath('body', 'Hello from user!');
        Event::assertDispatched(MessageSentEvent::class);
    }

    public function test_user_cannot_send_message_to_another_users_conversation(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);

        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $conversation = Conversation::create(['user_id' => $otherUser->id, 'book_id' => $book->id, 'status' => 'open']);

        $response = $this->actingAs($user)->postJson(route('chat.messages.store', $conversation->id), [
            'body' => 'Hacking attempt'
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_get_messages(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $conversation = Conversation::create(['user_id' => $user->id, 'book_id' => $book->id, 'status' => 'open']);

        Message::create(['conversation_id' => $conversation->id, 'user_id' => $user->id, 'body' => 'Test chat message']);

        $response = $this->actingAs($user)->getJson(route('chat.messages.index', $conversation->id));

        $response->assertStatus(200)->assertJsonPath('messages.0.body', 'Test chat message');
    }

    public function test_admin_can_close_conversation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $conversation = Conversation::create(['user_id' => $user->id, 'book_id' => $book->id, 'status' => 'open']);

        $response = $this->actingAs($admin)->patchJson(route('chat.conversation.close', $conversation->id));

        $response->assertStatus(200)->assertJsonPath('status', 'closed');
        $this->assertDatabaseHas('conversations', ['id' => $conversation->id, 'status' => 'closed']);
    }

    public function test_non_admin_cannot_close_conversation(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $conversation = Conversation::create(['user_id' => $user->id, 'book_id' => $book->id, 'status' => 'open']);

        $response = $this->actingAs($user)->patchJson(route('chat.conversation.close', $conversation->id));

        $response->assertStatus(403);
    }
}
