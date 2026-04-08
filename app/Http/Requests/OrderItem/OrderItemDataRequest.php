<?php

declare(strict_types=1);

namespace App\Http\Requests\OrderItem;

use App\Dto\OrderItem\OrderItemDto;
use Illuminate\Foundation\Http\FormRequest;

final class OrderItemDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orderId' => ['required', 'integer', 'exists:orders,id'],
            'bookId' => ['required', 'integer', 'exists:books,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'priceAtPurchase' => ['required', 'numeric', 'min:0', 'max:9999.99'],
        ];
    }

    public function toDto(): OrderItemDto
    {
        return new OrderItemDto(
            orderId: $this->integer('orderId'),
            bookId: $this->integer('bookId'),
            quantity: $this->integer('quantity'),
            priceAtPurchase: $this->float('priceAtPurchase'),
        );
    }
}
