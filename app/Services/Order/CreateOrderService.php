<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderResponseDto;
use App\Dto\OrderItem\OrderItemDto;
use App\Dto\OrderItem\OrderItemInputDto;
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
            $useDirectItems = $dto->items !== null;
            $items = $dto->items;

            if ($items === null) {
                $cartItems = $this->cartItemRepository->getAllByUserId($dto->userId);

                if ($cartItems === []) {
                    throw ValidationException::withMessages([
                        'cart' => 'Cannot create order: cart is empty.',
                    ]);
                }

                $items = [];

                foreach ($cartItems as $cartItem) {
                    if ($cartItem->book === null) {
                        throw ValidationException::withMessages([
                            'cart' => 'A book in your cart is no longer available.',
                        ]);
                    }

                    $items[] = new OrderItemInputDto(
                        bookId: $cartItem->bookId,
                        quantity: $cartItem->quantity,
                    );
                }
            }

            if ($items === []) {
                throw ValidationException::withMessages([
                    'items' => 'At least one item is required.',
                ]);
            }

            $bookIds = array_map(
                static fn (OrderItemInputDto $item): int => $item->bookId,
                $items,
            );

            $lockedBooks = $this->bookRepository->lockForUpdateByIds($bookIds);

            $total = '0.00';

            foreach ($items as $item) {
                $lockedBook = $lockedBooks[$item->bookId] ?? null;

                if ($lockedBook === null) {
                    throw ValidationException::withMessages([
                        'items' => "Book with ID {$item->bookId} is no longer available.",
                    ]);
                }

                if ($lockedBook->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Not enough stock for book: {$lockedBook->title}",
                    ]);
                }

                $price = is_numeric($lockedBook->price) ? $lockedBook->price : '0';
                $total = bcadd($total, bcmul($price, (string) $item->quantity, 2), 2);
            }

            $order = $this->orderRepository->create(new OrderDto(
                userId: $dto->userId,
                status: 'pending',
                shippingAddress: $dto->shippingAddress,
                paymentMethod: $dto->paymentMethod,
                total: $total,
            ));

            foreach ($items as $item) {
                $this->orderItemRepository->create(new OrderItemDto(
                    orderId: $order->id,
                    bookId: $item->bookId,
                    quantity: $item->quantity,
                    priceAtPurchase: $lockedBooks[$item->bookId]->price,
                ));

                $decremented = $this->bookRepository->decrementStock($item->bookId, $item->quantity);

                if (!$decremented) {
                    throw ValidationException::withMessages([
                        'stock' => "Not enough stock for book with ID {$item->bookId}",
                    ]);
                }
            }

            if (!$useDirectItems) {
                $this->cartItemRepository->deleteByUserId($dto->userId);
            }

            return $order;
        });

        return $orderResponse;
    }
}
