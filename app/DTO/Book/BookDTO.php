<?php

declare(strict_types=1);

namespace App\DTO\Book;

use App\Http\Requests\BookRequest;

final readonly class BookDTO
{
    public function __construct(
        public string $title,
        public ?string $slug,
        public int $author_id,
        public ?string $description,
        public float $price,
        public int $stock,
        public ?int $publish_year,
        public ?string $cover_image,
        public ?string $status,
    ) {}

    public static function fromRequest(BookRequest $request): self
    {
        return new self(
            title: (string) $request->string('title'),
            slug: $request->string('slug')->nullable(),
            author_id: $request->integer('author_id'),
            description: $request->string('description')->nullable(),
            price: $request->float('price'),
            stock: $request->integer('stock'),
            publish_year: $request->integer('publish_year', null),
            cover_image: $request->string('cover_image')->nullable(),
            status: $request->string('status')->nullable(),
        );
    }
}
