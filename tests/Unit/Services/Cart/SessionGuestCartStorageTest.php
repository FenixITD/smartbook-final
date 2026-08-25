<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Cart;

use App\Services\Cart\SessionGuestCartStorage;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SessionGuestCartStorageTest extends TestCase
{
    private SessionGuestCartStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = new SessionGuestCartStorage();
    }

    public function test_get_cart_returns_empty_array_initially(): void
    {
        $this->assertSame([], $this->storage->getCart());
    }

    public function test_get_cart_returns_array_when_set(): void
    {
        $cart = [1 => ['book_id' => 1, 'quantity' => 2]];
        Session::put('guest_cart', $cart);

        $this->assertSame($cart, $this->storage->getCart());
    }

    public function test_save_cart_stores_array_in_session(): void
    {
        $cart = [1 => ['book_id' => 1, 'quantity' => 2]];
        $this->storage->saveCart($cart);

        $this->assertSame($cart, Session::get('guest_cart'));
    }

    public function test_clear_removes_cart_from_session(): void
    {
        $cart = [1 => ['book_id' => 1, 'quantity' => 2]];
        Session::put('guest_cart', $cart);
        $this->storage->clear();

        $this->assertFalse(Session::has('guest_cart'));
    }
}
