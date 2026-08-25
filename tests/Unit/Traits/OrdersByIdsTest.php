<?php

declare(strict_types=1);

namespace Tests\Unit\Traits;

use App\Traits\OrdersByIds;
use Tests\TestCase;

final class OrdersByIdsTest extends TestCase
{
    use OrdersByIds;

    public function test_returns_1_for_empty_array(): void
    {
        $this->assertSame('1', $this->orderByIds([]));
    }

    public function test_returns_case_for_single_id(): void
    {
        $this->assertSame('CASE WHEN id = 5 THEN 0 ELSE 1 END', $this->orderByIds([5]));
    }

    public function test_returns_case_for_multiple_ids(): void
    {
        $result = $this->orderByIds([10, 20, 30]);

        $this->assertStringContainsString('WHEN id = 10 THEN 0', $result);
        $this->assertStringContainsString('WHEN id = 20 THEN 1', $result);
        $this->assertStringContainsString('WHEN id = 30 THEN 2', $result);
        $this->assertStringContainsString('ELSE 3 END', $result);
    }

    public function test_casts_non_integer_strings_to_integers(): void
    {
        $result = $this->orderByIds(['42', '7']);

        $this->assertStringContainsString('WHEN id = 42 THEN 0', $result);
        $this->assertStringContainsString('WHEN id = 7 THEN 1', $result);
    }

    public function test_returns_1_for_array_of_zeros(): void
    {
        $result = $this->orderByIds([0, 0]);

        $this->assertStringContainsString('WHEN id = 0 THEN 0', $result);
        $this->assertStringContainsString('WHEN id = 0 THEN 1', $result);
        $this->assertStringContainsString('ELSE 2 END', $result);
    }
}
