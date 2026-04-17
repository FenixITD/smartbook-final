<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Dto\Order\OrderFiltersDto;
use Illuminate\Foundation\Http\FormRequest;

final class OrderListRequest extends FormRequest
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

    public function toDto(): OrderFiltersDto
    {
        return new OrderFiltersDto(
            search: $this->has('search') ? (string) $this->string('search') : null,
            perPage: $this->integer('perPage', 15),
            sortBy: (string) $this->string('sortBy', 'id'),
            sortDirection: (string) $this->string('sortDirection', 'asc'),
        );
    }
}
