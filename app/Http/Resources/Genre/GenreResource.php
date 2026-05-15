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
        new OA\Property(property: 'name', type: 'string', example: 'Genre name'),
        new OA\Property(property: 'slug', type: 'string', example: 'genre-name'),
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
