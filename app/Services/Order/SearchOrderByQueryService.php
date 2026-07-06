<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Services\Abstracts\AbstractSearchByQueryService;

class SearchOrderByQueryService extends AbstractSearchByQueryService
{
    protected function getIndexConfigKey(): string
    {
        return 'elasticsearch.orders_index';
    }
}
