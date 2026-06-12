<?php

declare(strict_types=1);

namespace App\Http\Resources\Genre;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SearchSuggestGenreResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Fantasy'),
        new OA\Property(property: 'url', type: 'string', example: 'http://localhost:8000/api/genres/1'),
    ],
    type: 'object',
)]
final class SearchSuggestGenreResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'name' => $this['name'],
            'url' => $this['url'],
        ];
    }
}
