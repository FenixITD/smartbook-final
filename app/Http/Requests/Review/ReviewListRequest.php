<?php

declare(strict_types=1);

namespace App\Http\Requests\Review;

use App\Dto\Review\ReviewFiltersDto;
use Illuminate\Foundation\Http\FormRequest;

class ReviewListRequest extends FormRequest
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
            'sortBy' => ['nullable', 'string'],
            'sortDirection' => ['nullable', 'in:asc,desc'],
        ];
    }

    public function toDto(): ReviewFiltersDto
    {
        return new ReviewFiltersDto(
            search: $this->has('search') ? (string) $this->string('search') : null,
            perPage: $this->integer('perPage', 15),
            sortBy: (string) $this->string('sortBy', 'id'),
            sortDirection: (string) $this->string('sortDirection', 'asc'),
        );
    }
}
