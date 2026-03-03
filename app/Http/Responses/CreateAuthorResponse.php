<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\DTO\Author\AuthorResponseDTO;
use Illuminate\Http\JsonResponse;

final class CreateAuthorResponse extends JsonResponse
{
    public function __construct(AuthorResponseDTO $author)
    {
        parent::__construct(
            data: [
                'message' => 'Author created successfully',
                'author' => $author,
            ],
            status: 201
        );
    }
}
