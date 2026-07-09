<?php declare(strict_types=1);
namespace Tests\Feature\Services\Cart;
use App\Models\{Author, Book, User};
use App\Services\Cart\MergeSessionCartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MergeSessionCartServiceTest extends TestCase {
    use RefreshDatabase;
    public function test_execute_merges_cart_and_clears_session(): void {
        $user = User::factory()->create();
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'stock' => 10]);
        session(['guest_cart' => [$book->id => ['book_id' => $book->id, 'quantity' => 3]]]);
        $this->actingAs($user);
        $this->app->make(MergeSessionCartService::class)->execute();
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 3]);
        $this->assertEmpty(session('guest_cart'));
    }
}
