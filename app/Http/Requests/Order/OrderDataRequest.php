<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Dto\Order\OrderDto;
use Illuminate\Foundation\Http\FormRequest;

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
            'paymentMethod' => ['nullable', 'string', 'max:255'],
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
