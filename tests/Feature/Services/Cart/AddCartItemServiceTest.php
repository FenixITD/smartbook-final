<?php declare(strict_types=1);
namespace Tests\Feature\Services\Cart;
use App\Models\{Author, Book, CartItem, User};
use App\Services\Cart\AddCartItemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class AddCartItemServiceTest extends TestCase {
    use RefreshDatabase;
    public function test_adds_item_to_user_cart(): void {
        $user = User::factory()->create();
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'stock' => 10]);
        $this->actingAs($user);
        $this->app->make(AddCartItemService::class)->add($book->id, 2);
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 2]);
    }
    public function test_throws_validation_exception_if_exceeds_stock(): void {
        $user = User::factory()->create();
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 4]);
        $this->actingAs($user);
        $this->expectException(ValidationException::class);
        $this->app->make(AddCartItemService::class)->add($book->id, 2);
    }
}
