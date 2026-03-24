<?php

declare(strict_types=1);

namespace App\Dto\Book;

use App\Models\Book;

final readonly class BookResponseDto
{
    public function __construct(
        public int $id,
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
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(Book $book): self
    {
        return new self(
            id: $book->id,
            title: (string) $book->title,
            slug: (string) $book->slug,
            authorId: (int) $book->author_id,
            description: (string) $book->description,
            price: (float) $book->price,
            stock: (int) $book->stock,
            publishYear: (int) $book->publish_year ? (int) $book->publish_year : null,
            coverImage: (string) $book->cover_image,
            averageRating: (float) $book->average_rating,
            ratingsCount: (int) $book->ratings_count,
            status: (string) $book->status,
            createdAt: $book->created_at->toDateTimeString(),
            updatedAt: $book->updated_at->toDateTimeString(),
        );
    }
}
