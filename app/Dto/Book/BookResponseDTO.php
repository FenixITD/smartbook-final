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
            title: $book->title,
            slug: $book->slug,
            authorId: (int) $book->authorId,
            description: $book->description,
            price: (float) $book->price,
            stock: (int) $book->stock,
            publishYear: $book->publishYear ? (int) $book->publishYear : null,
            coverImage: $book->coverImage,
            averageRating: (float) $book->averageRating,
            ratingsCount: $book->ratingsCount,
            status: $book->status,
            createdAt: $book->created_at->toDateTimeString(),
            updatedAt: $book->updated_at->toDateTimeString(),
        );
    }
}
