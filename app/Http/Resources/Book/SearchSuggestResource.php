<?php

declare(strict_types=1);

namespace App\Http\Resources\Book;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array{id: int, title: string, author: string|null, cover_image: string|null, price: float, url: string} $resource
 */
final class SearchSuggestResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'title' => $this->resource['title'],
            'author' => $this->resource['author'],
            'cover_image' => $this->resource['cover_image'],
            'price' => $this->resource['price'],
            'url' => $this->resource['url'],
        ];
    }
}
