<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Dto\Auth\LoginDto;
use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    public function toDto(): LoginDto
    {
        return new LoginDto(
            email: (string) $this->string('email'),
            password: (string) $this->string('password'),
            remember: $this->boolean('remember'),
            ip: $this->ip(),
        );
    }
}
