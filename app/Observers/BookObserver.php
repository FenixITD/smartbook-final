<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Book;
use App\Services\Elasticsearch\BookIndexService;

final class BookObserver
{
    public function __construct(
        private readonly BookIndexService $indexService,
    ) {}

    public function created(Book $book): void
    {
        $this->indexService->indexBook($book);
    }

    public function updated(Book $book): void
    {
        $this->indexService->indexBook($book);
    }

    public function deleted(Book $book): void
    {
        $this->indexService->deleteBook($book->id);
    }
}
