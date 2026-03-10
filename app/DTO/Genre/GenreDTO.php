<?php

declare(strict_types=1);

namespace App\DTO\Genre;

use App\Http\Requests\Genre\GenreDataRequest;

final readonly class GenreDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $description,
    ) {}

    public static function fromRequest(GenreDataRequest $request): self
    {
        return new self(
            name: $request->string('name'),
            slug: $request->string('slug'),
            description: $request->string('description'),
        );
    }
}
