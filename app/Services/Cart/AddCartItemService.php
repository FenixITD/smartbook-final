<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AddCartItemService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private BookRepositoryInterface $bookRepository,
    ) {
    }

    public function add(int $bookId, int $quantity): void
    {
        $book = $this->bookRepository->getById($bookId);

        if (!$book) {
            throw ValidationException::withMessages(['cart' => 'Book not found.']);
        }

        $userId = (int) Auth::id();
        $cartItems = $this->repository->getAllByUserId($userId);

        $currentQuantity = 0;
        foreach ($cartItems as $item) {
            if ($item->bookId === $bookId) {
                $currentQuantity = $item->quantity;
                break;
            }
        }

        if ($currentQuantity + $quantity > $book->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Cannot add more. Only {$book->stock} available in stock."
            ]);
        }

        $this->repository->addOrIncrement(new CartItemDto(
            userId: $userId,
            bookId: $bookId,
            quantity: $quantity,
        ));

        activity('CartItem')
            ->withProperties(['user_id' => $userId, 'book_id' => $bookId, 'quantity' => $quantity])
            ->log('added');
    }
}
