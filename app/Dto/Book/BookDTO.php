<?php

declare(strict_types=1);

namespace App\DTO\Book;

use App\Http\Requests\Book\BookDataRequest;

final readonly class BookDTO
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

    public static function fromRequest(BookDataRequest $request): self
    {
        return new self(
            title: $request->string('title'),
            slug: $request->string('slug'),
            authorId: $request->integer('authorId'),
            description: $request->string('description'),
            price: $request->float('price'),
            stock: $request->integer('stock', 0),
            publishYear: $request->integer('publishYear', null),
            coverImage: $request->string('coverImage')->nullable(),
            averageRating: $request->float('averageRating', 0.00),
            ratingsCount: $request->integer('ratingsCount', 0),
            status: $request->string('status', 'active'),
        );
    }
}
