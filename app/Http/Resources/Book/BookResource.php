<?php

declare(strict_types=1);

namespace App\Http\Resources\Book;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
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
