<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Http\Requests\Book\BookListRequest;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\View\View;

final readonly class GetListBookWebController
{
    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
    }

    public function __invoke(BookListRequest $request): View
    {
        $books = $this->repository->getWebList($request->toDto());

        return view('books.list', compact('books'));
    }
}
