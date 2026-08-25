<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AddCartItemService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private BookRepositoryInterface $bookRepository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function add(int $bookId, int $quantity): void
    {
        $this->transactionManager->transaction(function () use ($bookId, $quantity): void {
            $userId = (int) Auth::id();

            $lockedBooks = $this->bookRepository->lockForUpdateByIds([$bookId]);
            $book = $lockedBooks[$bookId] ?? null;

            if ($book === null) {
                throw ValidationException::withMessages(['cart' => 'Book not found.']);
            }

            $currentQuantity = $this->repository->getQuantityByUserAndBook($userId, $bookId);

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
        });
    }
}
