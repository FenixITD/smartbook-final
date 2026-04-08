<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Models\Book;

final readonly class SyncBookGenresService
{
    public function execute(Book $book, array $genreIds): void
    {
        $book->genres()->sync($genreIds);
    }
}
