<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Http\Requests\Book\BookListRequest;
use App\Models\Book;
use Illuminate\View\View;

final readonly class GetListBookWebController
{
    public function __invoke(BookListRequest $request): View
    {
        $books = Book::with(['author', 'genres'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('books.list', compact('books'));
    }
}
