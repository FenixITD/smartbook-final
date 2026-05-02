<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Controllers\Controller;
use App\Http\Requests\Book\BookListRequest;
use App\Http\Resources\Book\BookResource;
use App\Services\Book\SearchBookService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/books',
    summary: 'Get a list of all books',
    tags: ['Books'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get a list of all books',
            content: new OA\JsonContent(
                ref: '#/components/schemas/BookResource'
            )
        ),
    ]
)]
final class GetListBookController extends Controller
{
    public function __construct(
        private SearchBookService $searchService,
    ) {
    }

    public function __invoke(BookListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $books = $this->searchService->getList($filters);

        return BookResource::collection($books)->response();
    }
}
