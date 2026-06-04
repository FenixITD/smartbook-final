<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Http\Requests\Book\BookWebDataRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Book\UpdateBookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class UpdateBookController
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private AuthorRepositoryInterface $authorRepository,
        private GenreRepositoryInterface $genreRepository,
        private UpdateBookService $service,
    ) {
    }

    public function edit(int $bookId): View
    {
        $book = $this->bookRepository->findByIdWithRelations($bookId);
        $authors = $this->authorRepository->getAll();
        $genres = $this->genreRepository->getAll();

        return view('books.edit', compact('book', 'authors', 'genres'));
    }

    public function update(BookWebDataRequest $request, int $bookId): RedirectResponse
    {
        $this->service->execute($bookId, $request->toDto(), $request->genres());

        return redirect()->route('books.index')
            ->with('success', 'Book updated successfully.');
    }
}
