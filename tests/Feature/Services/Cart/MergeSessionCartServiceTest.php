<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Cart;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use App\Services\Cart\MergeSessionCartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MergeSessionCartServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_merges_cart_and_clears_session(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'stock' => 10]);
        session(['guest_cart' => [$book->id => ['book_id' => $book->id, 'quantity' => 3]]]);
        $this->actingAs($user);
        $this->app->make(MergeSessionCartService::class)->execute();
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 3]);
        $this->assertEmpty(session('guest_cart'));
    }

    public function test_merges_up_to_available_stock_and_flashes_warning(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'stock' => 1, 'status' => 'active']);
        session(['guest_cart' => [$book->id => ['book_id' => $book->id, 'quantity' => 3]]]);
        $this->actingAs($user);
        $this->app->make(MergeSessionCartService::class)->execute();
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 1]);
        $this->assertTrue(session()->has('warning'));
        $this->assertStringContainsString($book->title, (string) session('warning'));
        $this->assertStringContainsString('Only 1', (string) session('warning'));
        $this->assertEmpty(session('guest_cart'));
    }

    public function test_drops_zero_stock_items_and_flashes_warning(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'stock' => 0, 'status' => 'active']);
        session(['guest_cart' => [$book->id => ['book_id' => $book->id, 'quantity' => 2]]]);
        $this->actingAs($user);
        $this->app->make(MergeSessionCartService::class)->execute();
        $this->assertDatabaseMissing('cart_items', ['user_id' => $user->id, 'book_id' => $book->id]);
        $this->assertTrue(session()->has('warning'));
        $this->assertStringContainsString($book->title, (string) session('warning'));
        $this->assertEmpty(session('guest_cart'));
    }

    public function test_skips_missing_books_with_warning(): void
    {
        $user = User::factory()->create();
        session(['guest_cart' => [999999 => ['book_id' => 999999, 'quantity' => 2]]]);
        $this->actingAs($user);
        $this->app->make(MergeSessionCartService::class)->execute();
        $this->assertDatabaseMissing('cart_items', ['user_id' => $user->id, 'book_id' => 999999]);
        $this->assertTrue(session()->has('warning'));
        $this->assertStringContainsString('no longer available', (string) session('warning'));
        $this->assertEmpty(session('guest_cart'));
    }

    public function test_caps_combined_quantity_when_user_already_has_items(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'stock' => 4, 'status' => 'active']);
        \App\Models\CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 2]);
        session(['guest_cart' => [$book->id => ['book_id' => $book->id, 'quantity' => 5]]]);
        $this->actingAs($user);
        $this->app->make(MergeSessionCartService::class)->execute();
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 4]);
        $this->assertTrue(session()->has('warning'));
        $this->assertStringContainsString('Only 4', (string) session('warning'));
    }

    public function test_rerun_after_merge_is_noop(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'stock' => 10, 'status' => 'active']);
        session(['guest_cart' => [$book->id => ['book_id' => $book->id, 'quantity' => 3]]]);
        $this->actingAs($user);
        $service = $this->app->make(MergeSessionCartService::class);
        $service->execute();
        session(['guest_cart' => []]);
        $service->execute();
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 3]);
        $this->assertFalse(session()->has('warning'));
    }
}
