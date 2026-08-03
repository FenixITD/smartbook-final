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
            'items' => ['required', 'array', 'min:1'],
            'items.*.bookId' => ['required', 'integer', 'exists:books,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toDto(): OrderDto
    {
        $items = array_map(function (mixed $item): OrderItemInputDto {
            $item = is_array($item) ? $item : [];
            $bookId = $item['bookId'] ?? 0;
            $quantity = $item['quantity'] ?? 1;

            return new OrderItemInputDto(
                bookId: is_int($bookId) ? $bookId : 0,
                quantity: is_int($quantity) ? $quantity : 1,
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
