<?php

declare(strict_types=1);

namespace App\Http\Requests\CartItem;

use App\Dto\CartItem\CartItemDto;
use Illuminate\Foundation\Http\FormRequest;

class CartItemDataRequest extends FormRequest
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
            'quantity' => ['required', 'integer'],
        ];
    }

    public function toDto(): CartItemDto
    {
        return new CartItemDto(
            userId: $this->integer('userId'),
            bookId: $this->integer('bookId'),
            quantity: $this->integer('quantity'),
        );
    }
}
