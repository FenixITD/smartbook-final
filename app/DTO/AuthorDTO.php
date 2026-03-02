<?php

declare(strict_types=1);

namespace App\DTO;

use App\Http\Requests\AuthorRequest;

final readonly class AuthorDTO
{
    public function __construct(
        public string $name,
    ) {}

    public static function fromRequest(AuthorRequest $request): self
    {
        return new self(
            name: (string) $request->string('name'),
        );
    }

    public static function filtersFromRequest(AuthorRequest $request): AuthorFiltersDTO
    {
        return new AuthorFiltersDTO(
            search: $request->string('search')->nullable(),
            perPage: $request->integer('per_page', 15),
            sortBy: (string) $request->string('sort_by', 'name'),
            sortDirection: (string) $request->string('sort_direction', 'asc'),
        );
    }
}

final readonly class AuthorFiltersDTO
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
        public string $sortBy = 'name',
        public string $sortDirection = 'asc',
    ) {}
}
