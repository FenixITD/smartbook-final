<?php

declare(strict_types=1);

namespace App\Dto\Auth;

final readonly class RegisterDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {
    }
}
