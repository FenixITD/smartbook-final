<?php

declare(strict_types=1);

namespace App\Http\Resources\Book;

use App\Dto\Book\BookResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Book title'),
        new OA\Property(property: 'slug', type: 'string', example: 'book-title'),
        new OA\Property(property: 'authorId', type: 'integer', example: 3),
        new OA\Property(property: 'description', type: 'string', example: 'Book description'),
        new OA\Property(property: 'price', type: 'number', example: 16.99),
        new OA\Property(property: 'stock', type: 'integer', example: 8),
        new OA\Property(property: 'publishYear', type: 'integer', example: 2012, nullable: true),
        new OA\Property(property: 'coverImage', type: 'string', example: 'Book image', nullable: true),
        new OA\Property(property: 'averageRating', type: 'number', example: 4.6, nullable: true),
        new OA\Property(property: 'ratingsCount', type: 'integer', example: 18, nullable: true),
        new OA\Property(property: 'status', type: 'string', example: 'archived'),
        new OA\Property(property: 'createdAt', type: 'string', example: '2024-01-01 12:00:00'),
        new OA\Property(property: 'updatedAt', type: 'string', example: '2024-01-01 12:00:00'),
    ],
    type: 'object',
)]
/**
 * @mixin BookResponseDto
 */
final class BookResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var BookResponseDto $resource */
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'title' => $resource->title,
            'slug' => $resource->slug,
            'authorId' => $resource->authorId,
            'description' => $resource->description,
            'price' => $resource->price,
            'stock' => $resource->stock,
            'publishYear' => $resource->publishYear,
            'coverImage' => $resource->coverImage,
            'averageRating' => $resource->averageRating,
            'ratingsCount' => $resource->ratingsCount,
            'status' => $resource->status,
            'createdAt' => $resource->createdAt,
            'updatedAt' => $resource->updatedAt,
        ];
    }
}
