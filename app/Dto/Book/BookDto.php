<?php

declare(strict_types=1);

namespace App\Dto\Book;

final readonly class BookDto
{
    public function __construct(
        public string $title,
        public string $slug,
        public int $authorId,
        public string $description,
        public float $price,
        public int $stock,
        public ?int $publishYear,
        public ?string $coverImage,
        public ?float $averageRating,
        public ?int $ratingsCount,
        public string $status,
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'author_id' => $this->authorId,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'publish_year' => $this->publishYear,
            'cover_image' => $this->coverImage,
            'average_rating' => $this->averageRating,
            'ratings_count' => $this->ratingsCount,
            'status' => $this->status,
        ];
    }
}
