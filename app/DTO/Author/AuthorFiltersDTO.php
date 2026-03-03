<?php

declare(strict_types=1);

namespace App\DTO\Author;

use App\Http\Requests\AuthorRequest;

final readonly class AuthorFiltersDTO
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
        public string $sortBy = 'name',
        public string $sortDirection = 'asc',
    ) {}

    public static function fromRequest(AuthorRequest $request): self
    {
        return new self(
            search: $request->string('search')->nullable(),
            perPage: $request->integer('per_page', 15),
            sortBy: (string) $request->string('sort_by', 'name'),
            sortDirection: (string) $request->string('sort_direction', 'asc'),
        );
    }
}
