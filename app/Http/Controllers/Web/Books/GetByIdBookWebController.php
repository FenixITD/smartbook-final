<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\View\View;

final readonly class GetByIdBookWebController
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function __invoke(int $book): View
    {
        $book = $this->repository->findModelWithRelations($book);

        return view('books.show', compact('book'));
    }
}
