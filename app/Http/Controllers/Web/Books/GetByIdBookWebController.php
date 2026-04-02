<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Models\Book;
use Illuminate\View\View;

final readonly class GetByIdBookWebController
{
    public function __invoke(Book $book): View
    {
        $book->load(['author', 'genres']);

        return view('books.show', compact('book'));
    }
}
