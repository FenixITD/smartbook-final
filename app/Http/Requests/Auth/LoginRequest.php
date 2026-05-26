<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Dto\Auth\LoginDto;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
        new OA\Property(property: 'password', type: 'string', example: 'secret123'),
        new OA\Property(property: 'remember', type: 'boolean', example: false),
    ],
    type: 'object',
)]
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
