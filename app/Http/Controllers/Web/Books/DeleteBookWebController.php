<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteBookWebController
{
    public function __construct(
        private BookRepositoryInterface $repository,
    ) {}

    public function __invoke(Book $book): RedirectResponse
    {
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $this->repository->delete($book);

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully.');
    }
}
