<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Dto\Order\OrderDto;
use App\Dto\OrderItem\OrderItemInputDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OrderDataRequest',
    required: ['userId', 'shippingAddress', 'items'],
    properties: [
        new OA\Property(property: 'userId', type: 'integer', example: 3),
        new OA\Property(property: 'shippingAddress', type: 'string', example: 'Pushkina 19'),
        new OA\Property(property: 'paymentMethod', type: 'string', example: 'cash'),
        new OA\Property(
            property: 'items',
            type: 'array',
            maxItems: 50,
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'bookId', type: 'integer', example: 4),
                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final class OrderDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'userId' => ['required', 'integer', 'exists:users,id'],
            'shippingAddress' => ['required', 'string', 'max:255'],
            'paymentMethod' => ['required', 'string', Rule::in(['cash', 'card', 'webpay'])],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.bookId' => ['required', 'integer', 'distinct', 'exists:books,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function toDto(): OrderDto
    {
        $items = array_map(function (mixed $item): OrderItemInputDto {
            $item = is_array($item) ? $item : [];

            return new OrderItemInputDto(
                bookId: (int) ($item['bookId'] ?? 0),
                quantity: (int) ($item['quantity'] ?? 1),
            );
        }, (array) $this->input('items', []));

        return new OrderDto(
            userId: $this->integer('userId'),
            status: 'pending',
            shippingAddress: (string) $this->string('shippingAddress'),
            paymentMethod: (string) $this->string('paymentMethod'),
            items: $items,
        );
    }
}
