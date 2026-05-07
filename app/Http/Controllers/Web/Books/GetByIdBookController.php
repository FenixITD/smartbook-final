<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\View\View;

final readonly class GetByIdBookController
{
    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $bookId): View
    {
        $book = $this->repository->findByIdWithRelations($bookId);

        return view('books.show', compact('book'));
    }
}
