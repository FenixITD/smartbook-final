<?php

declare(strict_types=1);

namespace App\Dto\Book;

use App\Dto\Genre\GenreResponseDto;
use App\Models\Book;

class BookResponseDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public int $authorId,
        public string|null $authorName,
        public string $description,
        public float $price,
        public int $stock,
        public int|null $publishYear,
        public string|null $coverImage,
        public float|null $averageRating,
        public int|null $ratingsCount,
        public string $status,
        public string $createdAt,
        public string $updatedAt,
        /** @var GenreResponseDto[] */
        public array $genres = [],
    ) {
    }

    public static function fromModel(Book $book): self
    {
        return new self(
            id: $book->id,
            title: $book->title,
            slug: $book->slug,
            authorId: $book->author_id,
            authorName: $book->relationLoaded('author') ? $book->author?->name : null,
            description: $book->description,
            price: $book->price,
            stock: $book->stock,
            publishYear: $book->publish_year,
            coverImage: $book->cover_image,
            averageRating: $book->average_rating,
            ratingsCount: $book->ratings_count,
            status: $book->status,
            createdAt: $book->created_at?->toDateTimeString() ?? '',
            updatedAt: $book->updated_at?->toDateTimeString() ?? '',
            genres: $book->relationLoaded('genres')
                ? $book->genres->map(static fn ($g) => GenreResponseDto::fromModel($g))->all()
                : [],
        );
    }
}
