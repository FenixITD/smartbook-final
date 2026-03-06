<?php

declare(strict_types=1);

namespace App\DTO\Book;

use App\Http\Requests\Book\BookDataRequest;

final readonly class BookDTO
{
    public function __construct(
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
    ) {}

    public static function fromRequest(BookDataRequest $request): self
    {
        return new self(
            title: $request->string('title'),
            slug: $request->string('slug'),
            author_id: $request->integer('author_id'),
            description: $request->string('description'),
            price: $request->float('price'),
            stock: $request->integer('stock', 0),
            publish_year: $request->integer('publish_year', null),
            cover_image: $request->string('cover_image')->nullable(),
            average_rating: $request->float('average_rating', 0.00),
            ratings_count: $request->integer('ratings_count', 0),
            status: $request->string('status', 'active'),
        );
    }
}
