<?php

declare(strict_types=1);

namespace App\Http\Requests\Favorite;

use App\Dto\Favorite\FavoriteDto;

class FavoriteDataDtoRequest extends FavoriteDataRequest
{
    public function toDto(): FavoriteDto
    {
        return new FavoriteDto(
            userId: $this->integer('userId'),
            bookId: $this->integer('bookId'),
        );
    }
}
