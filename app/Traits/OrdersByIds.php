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
        if ($ids === []) {
            return '1';
        }

        $cases = collect($ids)
            ->values()
            ->map(static fn (int $id, int $pos) => 'WHEN id = '.$id.' THEN '.$pos)
            ->implode(' ');

        return 'CASE '.$cases.' ELSE '.count($ids).' END';
    }
}
