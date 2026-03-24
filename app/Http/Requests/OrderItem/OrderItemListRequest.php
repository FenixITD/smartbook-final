<?php

declare(strict_types=1);

namespace App\Http\Requests\OrderItem;

use App\Dto\OrderItem\OrderItemFiltersDto;
use Illuminate\Foundation\Http\FormRequest;

final class OrderItemListRequest extends FormRequest
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

    public function toDto(): OrderItemFiltersDto
    {
        return new OrderItemFiltersDto(
            search: $this->input('search'),
            perPage: $this->integer('per_page', 15),
            sortBy: (string) $this->string('sort_by', 'id'),
            sortDirection: (string) $this->string('sort_direction', 'asc'),
        );
    }
}
