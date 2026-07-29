<?php

declare(strict_types=1);

namespace App\Dto\OrderItem;

final readonly class OrderItemInputDto
{
    public function __construct(
        public int $bookId,
        public int $quantity,
    ) {}
}
