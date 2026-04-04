<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Cart\MergeSessionCartService;
use Illuminate\Auth\Events\Login;

final readonly class MergeCartOnLogin
{
    public function __construct(
        private MergeSessionCartService $mergeSessionCartService,
    ) {}

    public function handle(Login $event): void
    {
        $this->mergeSessionCartService->execute();
    }
}
