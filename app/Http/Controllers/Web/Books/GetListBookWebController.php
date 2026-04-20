<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Books;

use App\Http\Requests\Book\BookListRequest;
use App\Services\Book\SearchBookService;
use Illuminate\View\View;

final readonly class GetListBookWebController
{
    public function __construct(
        private SearchBookService $searchService,
    ) {
    }

    public function __invoke(BookListRequest $request): View
    {
        $books = $this->searchService->getWebList($request->toDto());

        return view('books.list', compact('books'));
    }
}
