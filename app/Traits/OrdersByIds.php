<?php

declare(strict_types=1);

namespace App\Traits;

use function count;

trait OrdersByIds
{
    /**
     * @param  array<int>  $ids
     */
    private function orderByIds(array $ids): string
    {
        $cases = collect($ids)
            ->values()
            ->map(static fn (int $id, int $pos) => 'WHEN id = '.(int) $id.' THEN '.(int) $pos)
            ->implode(' ');

        return 'CASE '.$cases.' ELSE '.count($ids).' END';
    }
}
