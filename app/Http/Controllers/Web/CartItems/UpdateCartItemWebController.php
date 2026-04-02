<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Http\Requests\CartItem\UpdateCartWebRequest;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\RedirectResponse;

final readonly class UpdateCartItemWebController
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {}

    public function __invoke(UpdateCartWebRequest $request, CartItem $cartItem): RedirectResponse
    {
        $this->repository->updateQuantity($cartItem, $request->integer('quantity'));

        return back()->with('success', 'Cart updated.');
    }
}
