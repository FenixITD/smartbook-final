<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Http\Requests\CartItem\UpdateCartWebRequest;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Cart\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final readonly class UpdateCartItemController
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCartService,
        private BookRepositoryInterface $bookRepository,
    ) {
    }

    public function __invoke(UpdateCartWebRequest $request, int $bookId): RedirectResponse
    {
        $quantity = $request->integer('quantity');
        $book = $this->bookRepository->getById($bookId);

        if ($book !== null && $quantity > $book->stock) {
            return back()->withErrors([
                'quantity' => "Cannot update. Only {$book->stock} available in stock."
            ]);
        }

        if (Auth::check()) {
            $this->repository->updateByUserAndBook(
                (int) Auth::id(),
                $bookId,
                $quantity
            );

            activity('CartItem')
                ->withProperties([
                    'user_id' => Auth::id(),
                    'book_id' => $bookId,
                    'quantity' => $request->integer('quantity')
                ])
                ->log('updated');
        } else {
            $this->guestCartService->update($bookId, $quantity);
        }

        return back()->with('success', 'Cart updated.');
    }
}
