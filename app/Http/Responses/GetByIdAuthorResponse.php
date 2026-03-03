<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\DTO\Author\AuthorResponseDTO;
use Illuminate\Http\JsonResponse;

final class GetByIdAuthorResponse extends JsonResponse
{
    public function __construct(AuthorResponseDTO $author)
    {
        parent::__construct(
            data: [
                'message' => 'The author was found successfully',
                'author' => $author,
            ],
            status: 200
        );
    }
}
