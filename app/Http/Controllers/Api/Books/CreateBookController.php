<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Http\Controllers\Controller;
use App\Http\Requests\Book\BookDataRequest;
use App\Http\Resources\Book\BookResource;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class CreateBookController extends Controller
{
    public function __construct(
        private BookRepositoryInterface $repository,
    ) {
    }

    #[OA\Post(
        path: '/api/books',
        summary: 'Create book',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/BookDataRequest'
            ),
        ),
        tags: ['Books'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Get created book',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/BookResource'
                )
            ),
        ]
    )]
    public function __invoke(BookDataRequest $request): JsonResponse
    {
        $book = $this->repository->create($request->toDto());

        return (new BookResource($book))->response()->setStatusCode(201);
    }
}
