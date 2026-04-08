<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Services\Book\DeleteBookService;
use Illuminate\Http\RedirectResponse;

final readonly class DeleteBookWebController
{
    public function __construct(
        private DeleteBookService $service,
    ) {}

    public function __invoke(int $bookId): RedirectResponse
    {
        $this->service->execute($bookId);

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully.');
    }
}
