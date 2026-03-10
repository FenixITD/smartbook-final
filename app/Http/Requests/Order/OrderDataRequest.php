<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

final class OrderDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
}
