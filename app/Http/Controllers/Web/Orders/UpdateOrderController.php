<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Http\Requests\Order\OrderDataRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class UpdateOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function edit(int $orderId): View
    {
        $order = $this->repository->findByIdWithRelations($orderId);

        return view('orders.edit', compact('order'));
    }

    public function update(OrderDataRequest $request, int $orderId): RedirectResponse
    {
        $this->repository->update($orderId, $request->toDto());

        return redirect()->route('orders.index')
            ->with('success', 'Order updated successfully.');
    }
}
