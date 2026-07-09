<?php declare(strict_types=1);

namespace Tests\Feature\Web\Chat;

use App\Events\MessageSentEvent;
use App\Http\Controllers\Web\Chat\CloseConversationController;
use App\Http\Controllers\Web\Chat\GetMessageController;
use App\Http\Controllers\Web\Chat\OpenConversationController;
use App\Http\Controllers\Web\Chat\SendMessageController;
use App\Models\Author;
use App\Models\Book;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ChatWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)) {
            $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        }

        // Register isolated test routes to hit the controllers directly
        Route::post('/_test/chat/open/{book}', OpenConversationController::class);
        Route::post('/_test/chat/{conversation}/messages', SendMessageController::class);
        Route::get('/_test/chat/{conversation}/messages', GetMessageController::class);
        Route::post('/_test/chat/{conversation}/close', CloseConversationController::class);
    }

    public function test_admin_can_view_admin_conversations_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // This route works naturally from your app's web routes
        $response = $this->actingAs($admin)->get('/chat/admin');

        $response->assertStatus(200)->assertViewIs('chat.admin');
    }

    public function test_user_can_open_conversation(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->actingAs($user)->postJson("/_test/chat/open/{$book->id}");

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

        $response = $this->actingAs($user)->postJson("/_test/chat/{$conversation->id}/messages", [
            'body' => 'Hello from user!'
        ]);

        $response->assertStatus(201)->assertJsonPath('body', 'Hello from user!');
        Event::assertDispatched(MessageSentEvent::class);
    }

    public function test_user_cannot_send_message_to_another_users_conversation(): void
    {
        // Explicitly set the roles to 'user' so they are not evaluated as admins
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);

        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $conversation = Conversation::create(['user_id' => $otherUser->id, 'book_id' => $book->id, 'status' => 'open']);

        $response = $this->actingAs($user)->postJson("/_test/chat/{$conversation->id}/messages", [
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

        $response = $this->actingAs($user)->getJson("/_test/chat/{$conversation->id}/messages");

        $response->assertStatus(200)->assertJsonPath('messages.0.body', 'Test chat message');
    }

    public function test_admin_can_close_conversation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $conversation = Conversation::create(['user_id' => $user->id, 'book_id' => $book->id, 'status' => 'open']);

        $response = $this->actingAs($admin)->postJson("/_test/chat/{$conversation->id}/close");

        $response->assertStatus(200)->assertJsonPath('status', 'closed');
        $this->assertDatabaseHas('conversations', ['id' => $conversation->id, 'status' => 'closed']);
    }
}
