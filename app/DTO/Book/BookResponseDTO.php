<?php

declare(strict_types=1);

namespace App\DTO\Book;

use App\Models\Book;

final readonly class BookResponseDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public int $author_id,
        public string $description,
        public float $price,
        public int $stock,
        public ?int $publish_year,
        public ?string $cover_image,
        public ?float $average_rating,
        public ?int $ratings_count,
        public string $status,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(Book $book): self
    {
        return new self(
            id: $book->id,
            title: $book->title,
            slug: $book->slug,
            author_id: $book->author_id,
            description: $book->description,
            price: (float) $book->price,
            stock: (int) $book->stock,
            publish_year: $book->publish_year ? (int) $book->publish_year : null,
            cover_image: $book->cover_image,
            average_rating: (float) $book->average_rating,
            ratings_count: $book->ratings_count,
            status: $book->status,
            created_at: $book->created_at->toDateTimeString(),
            updated_at: $book->updated_at->toDateTimeString(),
        );
    }
}
