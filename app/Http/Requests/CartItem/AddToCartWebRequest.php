<?php

declare(strict_types=1);

namespace App\Http\Requests\CartItem;

use App\Dto\CartItem\CartItemDto;
use Illuminate\Foundation\Http\FormRequest;

final class AddToCartWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function toDto(): CartItemDto
    {
        return new CartItemDto(
            userId: auth()->id(),
            bookId: $this->integer('book_id'),
            quantity: $this->integer('quantity'),
        );
    }
}
