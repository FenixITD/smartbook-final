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
            title: $request->input('title'),
            slug: $request->input('slug'),
            author_id: $request->integer('author_id'),
            description: $request->input('description'),
            price: (float) $request->input('price'),
            stock: $request->integer('stock', 0),
            publish_year: $request->filled('publish_year') ? $request->integer('publish_year') : null,
            cover_image: $request->input('cover_image') ?? null,
            average_rating: (float) ($request->input('average_rating') ?? 0.00),
            ratings_count: $request->integer('ratings_count', 0),
            status: $request->input('status', 'active'),
        );
    }
}
