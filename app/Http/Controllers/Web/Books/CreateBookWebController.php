<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Http\Requests\Book\BookWebDataRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Book\CreateBookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class CreateBookWebController
{
    public function __construct(
        private AuthorRepositoryInterface $authorRepository,
        private GenreRepositoryInterface $genreRepository,
        private CreateBookService $service)
    {
    }

    public function create(): View
    {
        $authors = $this->authorRepository->all();
        $genres = $this->genreRepository->all();

        return view('books.create', compact('authors', 'genres'));
    }

    public function store(BookWebDataRequest $request): RedirectResponse
    {
        $this->service->execute($request);

        return redirect()->route('books.index')
            ->with('success', 'Book created successfully.');
    }
}
