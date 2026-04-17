<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use stdClass;

final readonly class GetCartItemsService
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
        private GuestCartService $service,
    ) {
    }

    /**
     * @return Collection<int, CartItem>|Collection<int, object{id: null, book_id: int, quantity: int, book: \App\Models\Book|null, user_id: null}&stdClass>
     */
    public function execute(): Collection
    {
        if (Auth::check()) {
            return $this->repository->getByUserId((int) Auth::id());
        }

        return $this->service->getItems();
    }
}
