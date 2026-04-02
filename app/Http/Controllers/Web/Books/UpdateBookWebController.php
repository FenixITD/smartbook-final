<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Http\Requests\Book\BookWebDataRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Services\Book\UpdateBookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class UpdateBookWebController
{
    public function __construct(
        private UpdateBookService $service,
    ) {}

    public function edit(Book $book): View
    {
        $book->load('genres');
        $authors = Author::orderBy('name')->get();
        $genres = Genre::orderBy('name')->get();

        return view('books.edit', compact('book', 'authors', 'genres'));
    }

    public function update(BookWebDataRequest $request, Book $book): RedirectResponse
    {
        $this->service->execute($book, $request->toDtoForUpdate($book));

        $book->genres()->sync($request->genres());

        return redirect()->route('books.index')
            ->with('success', 'Book updated successfully.');
    }
}
