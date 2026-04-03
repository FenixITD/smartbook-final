<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemDto;
use App\Models\Book;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

final class CartService
{
    private const SESSION_KEY = 'guest_cart';

    public function __construct(
        private readonly CartItemRepositoryInterface $repository,
    ) {}

    public function add(int $bookId, int $quantity = 1): void
    {
        if (Auth::check()) {
            $this->repository->addOrIncrement(new CartItemDto(
                userId: Auth::id(),
                bookId: $bookId,
                quantity: $quantity,
            ));
        } else {
            $cart = session(self::SESSION_KEY, []);
            if (isset($cart[$bookId])) {
                $cart[$bookId]['quantity'] += $quantity;
            } else {
                $cart[$bookId] = ['book_id' => $bookId, 'quantity' => $quantity];
            }
            session([self::SESSION_KEY => $cart]);
        }
    }

    public function update(int $bookId, int $quantity, ?CartItem $cartItem = null): void
    {
        if (Auth::check() && $cartItem) {
            $this->repository->updateQuantity($cartItem, $quantity);
        } else {
            $cart = session(self::SESSION_KEY, []);
            if (isset($cart[$bookId])) {
                $cart[$bookId]['quantity'] = $quantity;
                session([self::SESSION_KEY => $cart]);
            }
        }
    }

    public function remove(int $bookId, ?CartItem $cartItem = null): void
    {
        if (Auth::check() && $cartItem) {
            $this->repository->delete($cartItem);
        } else {
            $cart = session(self::SESSION_KEY, []);
            unset($cart[$bookId]);
            session([self::SESSION_KEY => $cart]);
        }
    }

    public function getItems(): Collection
    {
        if (Auth::check()) {
            return CartItem::with('book.author')
                ->where('user_id', Auth::id())
                ->get();
        }

        $cart = session(self::SESSION_KEY, []);
        if (empty($cart)) {
            return collect();
        }

        $bookIds = array_keys($cart);
        $books = Book::with('author')->whereIn('id', $bookIds)->get()->keyBy('id');

        return collect($cart)->map(function ($item) use ($books) {
            $book = $books->get($item['book_id']);

            return (object) [
                'id' => null,
                'book_id' => $item['book_id'],
                'quantity' => $item['quantity'],
                'book' => $book,
                'user_id' => null,
            ];
        })->filter(fn ($item) => $item->book !== null)->values();
    }

    public function total(): float
    {
        return $this->getItems()->sum(fn ($item) => $item->book->price * $item->quantity);
    }

    public function count(): int
    {
        if (Auth::check()) {
            return CartItem::where('user_id', Auth::id())->count();
        }

        return array_sum(array_column(session(self::SESSION_KEY, []), 'quantity'));
    }

    public function mergeSessionCartToUser(): void
    {
        $cart = session(self::SESSION_KEY, []);
        if (empty($cart)) {
            return;
        }

        foreach ($cart as $item) {
            $this->repository->addOrIncrement(new CartItemDto(
                userId: Auth::id(),
                bookId: $item['book_id'],
                quantity: $item['quantity'],
            ));
        }

        session()->forget(self::SESSION_KEY);
    }
}
