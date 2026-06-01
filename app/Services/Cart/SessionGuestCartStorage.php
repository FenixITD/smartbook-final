<?php

declare(strict_types=1);

namespace App\Services\Cart;

use function is_array;

class SessionGuestCartStorage implements GuestCartStorageInterface
{
    private const SESSION_KEY = 'guest_cart';

    /**
     * @return array<int, array{book_id: int, quantity: int}>
     */
    public function getCart(): array
    {
        $raw = session(self::SESSION_KEY, []);

        if (!is_array($raw)) {
            return [];
        }

        /** @var array<int, array{book_id: int, quantity: int}> $raw */
        return $raw;
    }

    public function saveCart(array $cart): void
    {
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
