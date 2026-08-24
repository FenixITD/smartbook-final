<?php declare(strict_types=1);
namespace Tests\Feature\Services\Book;
use App\Models\{Author, Book, OrderItem, Review, User};
use App\Services\Book\DeleteBookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class DeleteBookServiceTest extends TestCase {
    use RefreshDatabase;
    public function test_deletes_book_and_removes_cover_image_from_s3(): void {
        Storage::fake('s3');
        Storage::disk('s3')->put('covers/test-cover.jpg', 'image-content');
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'cover_image' => 'covers/test-cover.jpg']);
        $this->app->make(DeleteBookService::class)->execute($book->id);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        Storage::disk('s3')->assertMissing('covers/test-cover.jpg');
    }
    public function test_deletes_book_without_cover_image(): void {
        Storage::fake('s3');
        $book = Book::factory()->create(['author_id' => Author::factory()->create()->id, 'cover_image' => null]);
        $this->app->make(DeleteBookService::class)->execute($book->id);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
    public function test_deleting_book_removes_its_reviews_through_model_events(): void {
        Storage::fake('s3');
        $user = User::factory()->create();
        $secondUser = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $otherBook = Book::factory()->create(['author_id' => $author->id]);
        $firstReview = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 4]);
        $secondReview = Review::factory()->create(['user_id' => $secondUser->id, 'book_id' => $book->id, 'rating' => 5]);
        $keptReview = Review::factory()->create(['user_id' => $user->id, 'book_id' => $otherBook->id]);

        Event::fake(['eloquent.deleted: '.Review::class]);

        $this->app->make(DeleteBookService::class)->execute($book->id);

        $this->assertDatabaseMissing('reviews', ['id' => $firstReview->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $secondReview->id]);
        $this->assertDatabaseHas('reviews', ['id' => $keptReview->id]);

        Event::assertDispatched('eloquent.deleted: '.Review::class, 2);
    }
    public function test_deleting_ordered_book_is_rejected_and_cover_survives(): void {
        Storage::fake('s3');
        Storage::disk('s3')->put('covers/ordered-book.jpg', 'image-content');
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'cover_image' => 'covers/ordered-book.jpg']);
        $buyer = User::factory()->create();
        $order = \App\Models\Order::factory()->create(['user_id' => $buyer->id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id]);

        try {
            $this->app->make(DeleteBookService::class)->execute($book->id);
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException) {
        }

        $this->assertDatabaseHas('books', ['id' => $book->id]);
        Storage::disk('s3')->assertExists('covers/ordered-book.jpg');
    }
}

