<?php

declare(strict_types=1);

namespace App\Http\Resources\Genre;

use App\Dto\Genre\GenreResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GenreResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Genre name'),
        new OA\Property(property: 'slug', type: 'string', example: 'genre-name'),
        new OA\Property(property: 'createdAt', type: 'string', example: '2024-01-01 12:00:00'),
        new OA\Property(property: 'updatedAt', type: 'string', example: '2024-01-01 12:00:00'),
    ],
    type: 'object',
)]
/**
 * @mixin GenreResponseDto
 */
final class GenreResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var GenreResponseDto $resource */
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'slug' => $resource->slug,
            'createdAt' => $resource->createdAt,
            'updatedAt' => $resource->updatedAt,
        ];
    }
}
