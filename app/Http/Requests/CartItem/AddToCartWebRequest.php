<?php

declare(strict_types=1);

namespace App\Http\Requests\CartItem;

use Illuminate\Foundation\Http\FormRequest;

final class AddToCartWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }
}
