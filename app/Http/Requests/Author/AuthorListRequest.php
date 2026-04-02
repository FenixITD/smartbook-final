<?php

declare(strict_types=1);

namespace App\Http\Requests\Author;

use App\Dto\Author\AuthorFiltersDto;
use Illuminate\Foundation\Http\FormRequest;

final class AuthorListRequest extends FormRequest
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

    public function toDto(): AuthorFiltersDto
    {
        return new AuthorFiltersDto(
            search: $this->input('search'),
            perPage: $this->integer('perPage', 15),
            sortBy: (string) $this->string('sortBy', 'id'),
            sortDirection: (string) $this->string('sortDirection', 'asc'),
        );
    }
}
