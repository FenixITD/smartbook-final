<?php

declare(strict_types=1);

namespace App\Http\Requests\Favorite;

use App\Dto\Favorite\FavoriteFiltersDto;

class FavoriteListDtoRequest extends FavoriteListRequest
{
    public function toDto(): FavoriteFiltersDto
    {
        return new FavoriteFiltersDto(
            search: $this->input('search'),
            perPage: $this->integer('per_page', 15),
            sortBy: (string) $this->string('sort_by', 'id'),
            sortDirection: (string) $this->string('sort_direction', 'asc'),
        );
    }
}
