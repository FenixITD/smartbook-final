<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Http\Requests\Order\OrderListRequest;
use App\Services\Order\GetWebListOrderService;
use Illuminate\View\View;

final readonly class GetListOrderController
{
    public function __construct(
        private GetWebListOrderService $getWebListOrderService,
    ) {
    }

    public function __invoke(OrderListRequest $request): View
    {
        $paginated = $this->getWebListOrderService->get($request->toDto());

        return view('orders.list', compact('paginated'));
    }
}
