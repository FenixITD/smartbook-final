<?php

declare(strict_types=1);

namespace App\Http\Requests\OrderItem;

use App\Dto\OrderItem\OrderItemDto;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OrderItemDataRequest',
    required: ['orderId', 'bookId', 'quantity', 'priceAtPurchase'],
    properties: [
        new OA\Property(property: 'orderId', type: 'integer', example: 2),
        new OA\Property(property: 'bookId', type: 'integer', example: 3),
        new OA\Property(property: 'quantity', type: 'integer', example: 2),
        new OA\Property(property: 'priceAtPurchase', type: 'number', example: 16.99),
    ],
    type: 'object',
)]
final class OrderItemDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
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
            priceAtPurchase: $this->string('priceAtPurchase')->toString(),
        );
    }
}
