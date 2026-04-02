<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\CartItems;

use App\Http\Requests\CartItem\AddToCartWebRequest;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\RedirectResponse;

final readonly class AddToCartWebController
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {}

    public function __invoke(AddToCartWebRequest $request): RedirectResponse
    {
        $this->repository->addOrIncrement($request->toDto());

        return back()->with('success', 'Book added to cart.');
    }
}
