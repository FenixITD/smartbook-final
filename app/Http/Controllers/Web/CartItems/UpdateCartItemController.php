<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Http\Requests\CartItem\UpdateCartWebRequest;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Cart\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final readonly class UpdateCartItemController
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $guestCartService,
    ) {}

    public function __invoke(UpdateCartWebRequest $request, int $bookId): RedirectResponse
    {
        if (Auth::check()) {
            $this->repository->updateByUserAndBook(
                (int) Auth::id(),
                $bookId,
                $request->integer('quantity')
            );
        } else {
            $this->guestCartService->update($bookId, $request->integer('quantity'));
        }

        return back()->with('success', 'Cart updated.');
    }
}
