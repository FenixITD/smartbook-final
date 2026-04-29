<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Models\Order;
use Illuminate\View\View;

final readonly class GetOrderController
{
    public function __invoke(Order $order): View
    {
        return view('orders.show', compact('order'));
    }
}
