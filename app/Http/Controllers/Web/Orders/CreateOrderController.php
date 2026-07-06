<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Http\Requests\Order\OrderDataRequest;
use App\Services\Order\CreateOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class CreateOrderController
{
    public function __construct(
        private CreateOrderService $createOrderService,
    ) {
    }

    public function create(): View
    {
        return view('orders.create');
    }

    public function store(OrderDataRequest $request): RedirectResponse
    {
        $this->createOrderService->execute($request->toDto());

        return redirect()->route('orders.index')
            ->with('success', 'Order created successfully.');
    }
}
