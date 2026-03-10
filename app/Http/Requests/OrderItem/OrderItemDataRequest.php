<?php

declare(strict_types=1);

namespace App\Http\Requests\OrderItem;

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
}
