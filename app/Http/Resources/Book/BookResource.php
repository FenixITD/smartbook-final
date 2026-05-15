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
        new OA\Property(property: 'title', type: 'string', example: 'Book title'),
        new OA\Property(property: 'slug', type: 'string', example: 'book-title'),
        new OA\Property(property: 'authorId', type: 'integer', example: 3),
        new OA\Property(property: 'description', type: 'string', example: 'Book description'),
        new OA\Property(property: 'price', type: 'number', example: 16.99),
        new OA\Property(property: 'stock', type: 'integer', example: 8),
        new OA\Property(property: 'publishYear', type: 'integer', example: 2012),
        new OA\Property(property: 'coverImage', type: 'string', example: 'Book image'),
        new OA\Property(property: 'averageRating', type: 'number', example: 4.6),
        new OA\Property(property: 'ratingsCount', type: 'integer', example: 18),
        new OA\Property(property: 'status', type: 'string', example: 'archived'),
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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'authorId' => $this->authorId,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'publishYear' => $this->publishYear,
            'coverImage' => $this->coverImage,
            'averageRating' => $this->averageRating,
            'ratingsCount' => $this->ratingsCount,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
