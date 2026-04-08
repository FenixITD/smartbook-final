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

final readonly class CreateBookWebController
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private AuthorRepositoryInterface $authorRepository,
        private GenreRepositoryInterface $genreRepository,
        private SyncBookGenresService $syncGenresService, ) {}

    public function create(): View
    {
        $authors = $this->authorRepository->all();
        $genres = $this->genreRepository->all();

        return view('books.create', compact('authors', 'genres'));
    }

    public function store(BookWebDataRequest $request): RedirectResponse
    {
        $responseDto = $this->bookRepository->create($request->toDto());

        $book = $this->bookRepository->findModel($responseDto->id);
        $this->syncGenresService->execute($book, $request->genres());

        return redirect()->route('books.index')
            ->with('success', 'Book created successfully.');
    }
}
