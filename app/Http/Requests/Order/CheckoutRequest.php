<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Dto\Order\OrderDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

final class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'shippingAddress' => ['required', 'string', 'max:255'],
            'paymentMethod' => ['required', 'string', Rule::in(['cash', 'card', 'webpay'])],
        ];
    }

    public function toDto(): OrderDto
    {
        return new OrderDto(
            userId: (int) Auth::id(),
            status: 'pending',
            shippingAddress: (string) $this->string('shippingAddress'),
            paymentMethod: (string) $this->string('paymentMethod'),
        );
    }
}
