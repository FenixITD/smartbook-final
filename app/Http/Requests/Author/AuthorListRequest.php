<?php

declare(strict_types=1);

namespace App\Http\Requests\Author;

use App\Dto\Author\AuthorFiltersDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AuthorListRequest extends FormRequest
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
            'sortBy' => ['nullable', 'string', Rule::in(['id', 'name', 'created_at'])],
            'sortDirection' => ['nullable', 'in:asc,desc'],
        ];
    }

    public function toDto(): AuthorFiltersDto
    {
        return new AuthorFiltersDto(
            search: $this->has('search') ? (string) $this->string('search') : null,
            perPage: $this->integer('perPage', 15),
            sortBy: (string) $this->string('sortBy', 'id'),
            sortDirection: (string) $this->string('sortDirection', 'desc'),
        );
    }
}
