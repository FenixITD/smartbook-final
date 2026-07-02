<?php

declare(strict_types=1);

namespace App\Http\Resources\Author;

use App\Dto\Author\AuthorResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthorResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Author name'),
        new OA\Property(property: 'createdAt', type: 'string', example: '2024-01-01 12:00:00'),
        new OA\Property(property: 'updatedAt', type: 'string', example: '2024-01-01 12:00:00'),
    ],
    type: 'object',
)]
final class AuthorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var AuthorResponseDto $resource */
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'createdAt' => $resource->createdAt,
            'updatedAt' => $resource->updatedAt,
        ];
    }
}
