<?php

declare(strict_types=1);

namespace App\Http\Requests\Favorite;

use App\Dto\Favorite\FavoriteDto;
use Illuminate\Foundation\Http\FormRequest;

class FavoriteDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'userId' => ['required', 'integer', 'exists:users,id'],
            'bookId' => ['required', 'integer', 'exists:books,id'],
        ];
    }

    public function toDto(): FavoriteDto
    {
        return new FavoriteDto(
            userId: $this->integer('userId'),
            bookId: $this->integer('bookId'),
        );
    }
}
