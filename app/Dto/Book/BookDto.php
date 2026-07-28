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
        public string $price,
        public int $stock,
        public int|null $publishYear,
        public string|null $coverImage,
        public string $status,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'author_id' => $this->authorId,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'publish_year' => $this->publishYear,
            'status' => $this->status,
        ];

        if ($this->coverImage !== null) {
            $data['cover_image'] = $this->coverImage;
        }

        return $data;
    }
}
