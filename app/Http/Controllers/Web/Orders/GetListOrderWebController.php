<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Http\Requests\Order\OrderListRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\View\View;

final readonly class GetListOrderWebController
{
    public function __construct(
        private OrderRepositoryInterface $repository,
    ) {
    }

    public function __invoke(OrderListRequest $request): View
    {
        $paginated = $this->repository->getWebList($request->toDto());

        return view('orders.list', compact('paginated'));
    }
}
