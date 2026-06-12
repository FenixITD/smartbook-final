<?php

declare(strict_types=1);

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SearchSuggestReviewResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'content', type: 'string', example: 'Amazing read...'),
        new OA\Property(property: 'url', type: 'string', example: 'http://localhost:8000/api/reviews/1'),
    ],
    type: 'object',
)]
final class SearchSuggestReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'user_name' => $this['user_name'],
            'content' => $this['content'],
            'url' => $this['url'],
        ];
    }
}
