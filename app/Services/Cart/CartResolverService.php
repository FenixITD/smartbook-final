<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Services\Interfaces\CartServiceInterface;
use Illuminate\Support\Facades\Auth;

final readonly class CartResolverService
{
    public function __construct(
        private AuthCartService $authCartService,
        private GuestCartService $guestCartService,
    ) {
    }

    public function resolve(): CartServiceInterface
    {
        return Auth::check()
            ? $this->authCartService
            : $this->guestCartService;
    }
}
