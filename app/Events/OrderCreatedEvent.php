<?php

declare(strict_types=1);

namespace App\Events;

use App\Dto\Order\OrderStatusChangedDto;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrderCreatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly OrderStatusChangedDto $dto,
    ) {}
}
