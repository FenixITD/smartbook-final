<?php

declare(strict_types=1);

namespace App\Http\Requests\Favorite;

use Illuminate\Foundation\Http\FormRequest;

final class FavoriteDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'userId' => ['required', 'integer', 'exists:users,id'],
            'bookId' => ['required', 'integer', 'exists:books,id'],
        ];
    }
}
