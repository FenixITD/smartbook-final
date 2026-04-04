<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Dto\Dashboard\DashboardFiltersDto;
use Illuminate\Foundation\Http\FormRequest;

final class DashboardListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'integer', 'exists:genres,id'],
            'author' => ['nullable', 'integer', 'exists:authors,id'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'status' => ['nullable', 'string', 'in:active,draft,archived'],
            'sort' => ['nullable', 'string', 'in:rating,newest,price_asc,price_desc'],
        ];
    }

    public function toDto(): DashboardFiltersDto
    {
        return new DashboardFiltersDto(
            search: $this->input('search'),
            genre: $this->integer('genre') ?: null,
            author: $this->integer('author') ?: null,
            year: $this->integer('year') ?: null,
            status: $this->input('status'),
            sort: $this->input('sort', 'rating'),
            perPage: 18,
        );
    }
}
