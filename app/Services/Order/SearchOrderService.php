<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Services\Abstracts\AbstractSearchService;

class SearchOrderService extends AbstractSearchService
{
    protected function getIndexConfigKey(): string
    {
        return 'elasticsearch.orders_index';
    }
}
