<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Dto\Order\OrderDto;
use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OrderUpdateRequest',
    required: ['userId', 'status', 'shippingAddress', 'paymentMethod'],
    properties: [
        new OA\Property(property: 'userId', type: 'integer', example: 3),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'paid', 'shipped', 'delivered', 'cancelled'], example: 'paid'),
        new OA\Property(property: 'shippingAddress', type: 'string', example: 'Pushkina 19'),
        new OA\Property(property: 'paymentMethod', type: 'string', example: 'cash'),
    ],
    type: 'object',
)]
final class OrderUpdateRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in(array_column(OrderStatusEnum::cases(), 'value'))],
            'shippingAddress' => ['required', 'string', 'max:255'],
            'paymentMethod' => ['required', 'string', Rule::in(['cash', 'card', 'webpay'])],
        ];
    }

    public function toDto(): OrderDto
    {
        return new OrderDto(
            userId: $this->integer('userId'),
            status: (string) $this->string('status'),
            shippingAddress: (string) $this->string('shippingAddress'),
            paymentMethod: (string) $this->string('paymentMethod'),
        );
    }
}
