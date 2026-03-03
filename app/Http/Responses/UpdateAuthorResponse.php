<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\DTO\Author\AuthorResponseDTO;
use Illuminate\Http\JsonResponse;

final class UpdateAuthorResponse extends JsonResponse
{
    public function __construct(AuthorResponseDTO $author)
    {
        parent::__construct(
            data: [
                'message' => 'Author updated successfully',
                'author' => $author,
            ],
            status: 200
        );
    }
}
