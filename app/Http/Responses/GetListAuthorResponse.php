<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class GetListAuthorResponse extends JsonResponse
{
    public function __construct(array $authors)
    {
        parent::__construct(
            data: [
                'message' => 'Authors list retrieved successfully',
                'authors' => $authors,
            ],
            status: 200
        );
    }
}
