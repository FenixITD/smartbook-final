<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class DeleteAuthorResponse extends JsonResponse
{
    public function __construct()
    {
        parent::__construct(
            data: [
                'success' => true,
                'message' => 'Author deleted successfully',
            ],
            status: 200
        );
    }
}
