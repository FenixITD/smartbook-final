<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Dto\Auth\ApiLoginDto;
use Illuminate\Foundation\Http\FormRequest;

final class ApiLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function toDto(): ApiLoginDto
    {
        return new ApiLoginDto(
            email: (string) $this->string('email'),
            password: (string) $this->string('password'),
            ip: $this->ip(),
        );
    }
}
