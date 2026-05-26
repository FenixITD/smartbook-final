<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Dto\Dashboard\DashboardFiltersDto;
use Illuminate\Foundation\Http\FormRequest;

final class DashboardListRequest extends FormRequest
{
    private const PER_PAGE = 18;
    private const DEFAULT_SORT = 'rating';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'integer', 'exists:genres,id'],
            'author' => ['nullable', 'integer', 'exists:authors,id'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'status' => ['nullable', 'string', 'in:active,draft,archived'],
            'sort' => ['nullable', 'string', 'in:rating,newest,price_asc,price_desc'],
        ];
    }

    public function toDto(): DashboardFiltersDto
    {
        return new DashboardFiltersDto(
            search: $this->filled('search') ? $this->str('search')->toString() : null,
            genre: $this->filled('genre') ? $this->integer('genre') : null,
            author: $this->filled('author') ? $this->integer('author') : null,
            year: $this->filled('year') ? $this->integer('year') : null,
            status: $this->filled('status') ? $this->str('status')->toString() : null,
            sort: $this->str('sort', DashboardFiltersDto::DEFAULT_SORT)->toString(),
            perPage: DashboardFiltersDto::DEFAULT_PER_PAGE,
        );
    }
}
