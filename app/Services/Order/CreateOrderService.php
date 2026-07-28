<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderResponseDto;
use App\Dto\OrderItem\OrderItemDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Validation\ValidationException;

class CreateOrderService
{
    public function __construct(
        private CartItemRepositoryInterface $cartItemRepository,
        private BookRepositoryInterface $bookRepository,
        private OrderRepositoryInterface $orderRepository,
        private OrderItemRepositoryInterface $orderItemRepository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function execute(OrderDto $dto): OrderResponseDto
    {
        /** @var OrderResponseDto $orderResponse */
        $orderResponse = $this->transactionManager->transaction(function () use ($dto): OrderResponseDto {
            $cartItems = $this->cartItemRepository->getAllByUserId($dto->userId);

            if ($cartItems === []) {
                throw ValidationException::withMessages([
                    'cart' => 'Cannot create order: cart is empty.',
                ]);
            }

            $bookIds = [];

            foreach ($cartItems as $item) {
                if ($item->book !== null) {
                    $bookIds[] = $item->bookId;
                }
            }

            $lockedBooks = $bookIds !== [] ? $this->bookRepository->lockForUpdateByIds($bookIds) : [];

            $total = '0.00';

            foreach ($cartItems as $item) {
                if ($item->book === null) {
                    throw ValidationException::withMessages([
                        'cart' => "A book in your cart is no longer available.",
                    ]);
                }

                $lockedBook = $lockedBooks[$item->bookId] ?? null;

                if ($lockedBook === null) {
                    throw ValidationException::withMessages([
                        'cart' => "A book in your cart is no longer available.",
                    ]);
                }

                if ($lockedBook->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Not enough stock for book: {$lockedBook->title}",
                    ]);
                }

                $total = bcadd($total, bcmul($lockedBook->price, (string) $item->quantity, 2), 2);
            }

            $orderDto = new OrderDto(
                userId: $dto->userId,
                status: $dto->status,
                shippingAddress: $dto->shippingAddress,
                paymentMethod: $dto->paymentMethod,
                total: $total,
            );

            $order = $this->orderRepository->create($orderDto);

            foreach ($cartItems as $item) {
                if ($item->book === null) {
                    continue;
                }

                $this->orderItemRepository->create(new OrderItemDto(
                    orderId: $order->id,
                    bookId: $item->bookId,
                    quantity: $item->quantity,
                    priceAtPurchase: $lockedBooks[$item->bookId]->price,
                ));

                $decremented = $this->bookRepository->decrementStock($item->bookId, $item->quantity);

                if (!$decremented) {
                    throw ValidationException::withMessages([
                        'stock' => "Not enough stock for book: {$item->book->title}",
                    ]);
                }
            }

            $this->cartItemRepository->deleteByUserId($dto->userId);

            return $order;
        });

        return $orderResponse;
    }
}
