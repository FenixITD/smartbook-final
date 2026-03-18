<?php

declare(strict_types=1);

namespace App\Dto\Genre;

use App\Http\Requests\Genre\GenreDataRequest;

final readonly class GenreDto
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $description,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
        ];
    }
}
