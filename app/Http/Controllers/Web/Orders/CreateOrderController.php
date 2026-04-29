<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Http\Requests\Order\OrderDataRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class CreateOrderController
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function create(): View
    {
        return view('orders.create');
    }

    public function store(OrderDataRequest $request): RedirectResponse
    {
        $this->repository->create($request->toDto());

        return redirect()->route('orders.index')
            ->with('success', 'Order created successfully.');
    }
}
