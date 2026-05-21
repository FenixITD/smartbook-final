<?php

declare(strict_types=1);

namespace App\Dto\User;

final readonly class UserCredentialsDto
{
    public function __construct(
        public int $id,
        public string $password,
    ) {
    }
}
