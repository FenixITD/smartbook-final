<?php

declare(strict_types=1);

namespace App\Http\Requests\Genre;

use App\Dto\Genre\GenreFiltersDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GenreListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sortBy' => ['nullable', 'string', Rule::in(['id', 'name', 'slug', 'created_at'])],
            'sortDirection' => ['nullable', 'in:asc,desc'],
        ];
    }

    public function toDto(): GenreFiltersDto
    {
        return new GenreFiltersDto(
            search: $this->has('search') ? (string) $this->string('search') : null,
            perPage: $this->integer('perPage', 15),
            sortBy: (string) $this->string('sortBy', 'id'),
            sortDirection: (string) $this->string('sortDirection', 'asc'),
        );
    }
}
