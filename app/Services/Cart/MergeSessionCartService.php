<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MergeSessionCartService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCartService,
        private TransactionManagerInterface $transactionManager,
        private BookRepositoryInterface $bookRepository,
    ) {
    }

    public function execute(): void
    {
        $guestCart = $this->guestCartService->getAll();

        if ($guestCart === []) {
            return;
        }

        $userId = (int) Auth::id();

        $lock = Cache::lock("merge_cart_{$userId}", 5);

        if ($lock->get() === false) {
            return;
        }

        try {
            $this->transactionManager->transaction(function () use ($userId, $guestCart): void {
                $userCartItems = $this->repository->getAllByUserId($userId);

                $userQuantities = [];
                foreach ($userCartItems as $item) {
                    $userQuantities[$item->bookId] = $item->quantity;
                }

                $bookIds = array_keys($guestCart);
                $books = $this->bookRepository->findByIdsWithAuthor($bookIds);

                $booksById = [];
                foreach ($books as $book) {
                    $booksById[$book->id] = $book;
                }

                $warnings = [];

                foreach ($guestCart as $bookId => $guestItem) {
                    $book = $booksById[$bookId] ?? null;

                    if ($book === null) {
                        $warnings[] = 'A book in your cart is no longer available.';

                        continue;
                    }

                    $currentQty = $userQuantities[$bookId] ?? 0;
                    $guestQty = $guestItem['quantity'];

                    $newQty = min($book->stock, $currentQty + $guestQty);

                    if ($newQty < $currentQty + $guestQty) {
                        $warnings[] = $newQty === 0
                            ? "\"{$book->title}\" is no longer available and was removed from your cart."
                            : "Only {$newQty} of \"{$book->title}\" available in stock. Your cart has been updated.";
                    }

                    if ($newQty > 0) {
                        if (isset($userQuantities[$bookId])) {
                            $this->repository->updateByUserAndBook($userId, $bookId, $newQty);
                        } else {
                            $this->repository->create(new CartItemDto(
                                userId: $userId,
                                bookId: $bookId,
                                quantity: $newQty,
                            ));
                        }
                    }
                }

                if ($warnings !== []) {
                    session()->flash('warning', implode(' ', $warnings));
                }

                $this->guestCartService->clear();
            });
        } finally {
            $lock->release();
        }
    }
}
