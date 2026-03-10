<?php

declare(strict_types=1);

namespace App\Http\Requests\Favorite;

use Illuminate\Foundation\Http\FormRequest;

final class FavoriteListRequest extends FormRequest
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
}
