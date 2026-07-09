<?php declare(strict_types=1);
namespace Tests\Feature\Services\Cart;
use App\Models\{Author, Book};
use App\Services\Cart\GuestCartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GuestCartServiceTest extends TestCase {
    use RefreshDatabase;
    public function test_add_saves_new_item_to_storage(): void {
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'stock' => 10]);
        $this->app->make(GuestCartService::class)->add($book->id, 3);
        $this->assertEquals(3, session('guest_cart')[$book->id]['quantity']);
    }
    public function test_update_saves_updated_item(): void {
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'stock' => 10]);
        session(['guest_cart' => [$book->id => ['book_id' => $book->id, 'quantity' => 2]]]);
        $this->app->make(GuestCartService::class)->update($book->id, 5);
        $this->assertEquals(5, session('guest_cart')[$book->id]['quantity']);
    }
}
