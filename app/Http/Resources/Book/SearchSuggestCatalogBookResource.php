<?php

declare(strict_types=1);

namespace App\Http\Resources\Book;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SearchSuggestCatalogBookResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Harry Potter'),
        new OA\Property(property: 'author', type: 'string', example: 'J.K. Rowling', nullable: true),
        new OA\Property(property: 'cover_image', type: 'string', example: 'covers/harry.jpg', nullable: true),
        new OA\Property(property: 'price', type: 'number', example: 19.99),
        new OA\Property(property: 'url', type: 'string', example: 'http://localhost/catalog/harry-potter'),
    ],
    type: 'object',
)]
final class SearchSuggestCatalogBookResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'title' => $this['title'],
            'author' => $this['author'],
            'cover_image' => $this['cover_image'],
            'price' => $this['price'],
            'url' => $this['url'],
        ];
    }
}
