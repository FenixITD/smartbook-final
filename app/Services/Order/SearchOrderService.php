<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Services\Abstracts\AbstractSearchService;
use stdClass;

class SearchOrderService extends AbstractSearchService
{
    protected function getIndexConfigKey(): string
    {
        return 'elasticsearch.orders_index';
    }

    /**
     * @return array<int, string>
     */
    protected function getSearchFields(): array
    {
        return ['user_name'];
    }
}
