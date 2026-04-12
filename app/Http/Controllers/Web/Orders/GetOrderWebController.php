<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Orders;

use App\Models\Order;
use Illuminate\View\View;

final readonly class GetOrderWebController
{
    public function __invoke(Order $order): View
    {
        return view('orders.show', compact('order'));
    }
}
