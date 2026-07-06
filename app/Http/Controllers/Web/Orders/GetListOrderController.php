<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Http\Requests\Order\OrderListRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\Order\SearchOrderService;
use App\Traits\HandlesWebListSearch;
use Illuminate\View\View;

final readonly class GetListOrderController
{
    use HandlesWebListSearch;

    public function __construct(
        private OrderRepositoryInterface $repository,
        private SearchOrderService $searchService,
    ) {
    }

    public function __invoke(OrderListRequest $request): View
    {
        $paginated = $this->handleWebListSearch($request->toDto(), $this->repository, $this->searchService);

        return view('orders.list', compact('paginated'));
    }
}
