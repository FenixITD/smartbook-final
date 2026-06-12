<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Dto\Order\OrderDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OrderDataRequest',
    required: ['userId', 'total', 'status', 'shippingAddress'],
    properties: [
        new OA\Property(property: 'userId', type: 'integer', example: 3),
        new OA\Property(property: 'total', type: 'number', example: 196.54),
        new OA\Property(property: 'status', type: 'string', example: 'delivered'),
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
            'total' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'status' => ['required', 'string', 'in:pending,paid,shipped,delivered,cancelled'],
            'shippingAddress' => ['required', 'string', 'max:255'],
            'paymentMethod' => ['required', 'string', Rule::in(['cash', 'card', 'webpay'])],
        ];
    }

    public function toDto(): OrderDto
    {
        return new OrderDto(
            userId: $this->integer('userId'),
            total: $this->float('total'),
            status: (string) $this->string('status'),
            shippingAddress: (string) $this->string('shippingAddress'),
            paymentMethod: (string) $this->string('paymentMethod'),
        );
    }
}
