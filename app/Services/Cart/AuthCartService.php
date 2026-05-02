<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemWithBookResponseDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Interfaces\CartServiceInterface;
use Illuminate\Support\Facades\Auth;

final readonly class AuthCartService implements CartServiceInterface
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {
    }

    /** @return array<int, array{book_id: int, quantity: int}> */
    public function getAll(): array
    {
        return [];
    }

    /** @return array<CartItemWithBookResponseDto> */
    public function getItems(): array
    {
        return $this->repository->getAllByUserId((int) Auth::id());
    }

    public function getTotal(): float
    {
        return $this->repository->getTotalByUserId((int) Auth::id());
    }

    public function add(int $bookId, int $quantity): void
    {
        $this->repository->addOrIncrement(new CartItemDto(
            userId: (int) Auth::id(),
            bookId: $bookId,
            quantity: $quantity,
        ));
    }

    public function update(int $bookId, int $quantity): void
    {
        $this->repository->updateByUserAndBook((int) Auth::id(), $bookId, $quantity);
    }

    public function remove(int $bookId): void
    {
        $this->repository->deleteByUserAndBook((int) Auth::id(), $bookId);
    }

    public function clear(): void
    {
        $this->repository->deleteByUserId((int) Auth::id());
    }

    public function count(): int
    {
        return $this->repository->countByUserId((int) Auth::id());
    }
}
