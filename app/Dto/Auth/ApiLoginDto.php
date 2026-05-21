<?php

declare(strict_types=1);

namespace App\Dto\Auth;

final readonly class ApiLoginDto
{
    public function __construct(
        public string $email,
        public string $password,
        public string|null $ip,
    ) {
    }
}
