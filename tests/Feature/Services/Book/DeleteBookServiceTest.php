<?php declare(strict_types=1);
namespace Tests\Feature\Services\Book;
use App\Models\{Author, Book};
use App\Services\Book\DeleteBookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
}
