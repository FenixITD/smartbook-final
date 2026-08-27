<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Cart\MergeSessionCartService;

final readonly class MergeCartOnLoginListener
{
    public function __construct(
        private MergeSessionCartService $mergeSessionCartService,
    ) {
    }

    public function handle(): void
    {
        $this->mergeSessionCartService->execute();
    }
}
