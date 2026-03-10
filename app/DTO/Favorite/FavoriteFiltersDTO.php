<?php

declare(strict_types=1);

namespace App\DTO\Favorite;

use App\Http\Requests\Favorite\FavoriteListRequest;

final readonly class FavoriteFiltersDTO
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
        public string $sortBy = 'id',
        public string $sortDirection = 'asc',
    ) {}

    public static function fromRequest(FavoriteListRequest $request): self
    {
        return new self(
            search: $request->input('search'),
            perPage: $request->integer('per_page', 15),
            sortBy: (string) $request->string('sort_by', 'id'),
            sortDirection: (string) $request->string('sort_direction', 'asc'),
        );
    }
}
