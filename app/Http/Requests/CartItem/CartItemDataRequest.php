<?php

declare(strict_types=1);

namespace App\Http\Requests\CartItem;

use Illuminate\Foundation\Http\FormRequest;

final class CartItemDataRequest extends FormRequest
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
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
