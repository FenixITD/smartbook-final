<?php

declare(strict_types=1);

namespace App\Http\Requests\Favorite;

use App\Dto\Favorite\FavoriteFiltersDto;
use Illuminate\Foundation\Http\FormRequest;

class FavoriteListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sortBy' => ['nullable', 'string'],
            'sortDirection' => ['nullable', 'in:asc,desc'],
        ];
    }

    public function toDto(): FavoriteFiltersDto
    {
        return new FavoriteFiltersDto(
            search: $this->input('search'),
            perPage: $this->integer('perPage', 15),
            sortBy: (string) $this->string('sortBy', 'id'),
            sortDirection: (string) $this->string('sortDirection', 'asc'),
        );
    }
}
