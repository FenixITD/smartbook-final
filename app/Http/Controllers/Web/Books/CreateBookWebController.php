<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Http\Requests\Book\BookWebDataRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Services\Book\CreateBookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class CreateBookWebController
{
    public function __construct(
        private CreateBookService $service,
    ) {}

    public function create(): View
    {
        $authors = Author::orderBy('name')->get();
        $genres = Genre::orderBy('name')->get();

        return view('books.create', compact('authors', 'genres'));
    }

    public function store(BookWebDataRequest $request): RedirectResponse
    {
        $dto = $request->toDto();
        $responseDto = $this->service->execute($dto);

        Book::findOrFail($responseDto->id)->genres()->sync($request->genres());

        return redirect()->route('books.index')
            ->with('success', 'Book created successfully.');
    }
}
