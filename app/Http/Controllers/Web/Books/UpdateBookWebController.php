<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Http\Requests\Book\BookWebDataRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Book\SyncBookGenresService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class UpdateBookWebController
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private AuthorRepositoryInterface $authorRepository,
        private GenreRepositoryInterface $genreRepository,
        private SyncBookGenresService $syncGenresService,
    ) {}

    public function edit(int $bookId): View
    {
        $book = $this->bookRepository->findModel($bookId);
        $book->load('genres');
        $authors = $this->authorRepository->all();
        $genres = $this->genreRepository->all();

        return view('books.edit', compact('book', 'authors', 'genres'));
    }

    public function update(BookWebDataRequest $request, int $bookId): RedirectResponse
    {
        $book = $this->bookRepository->findModel($bookId);

        $this->bookRepository->update($bookId, $request->toDtoForUpdate($book));
        $this->syncGenresService->execute($book, $request->genres());

        return redirect()->route('books.index')
            ->with('success', 'Book updated successfully.');
    }
}
