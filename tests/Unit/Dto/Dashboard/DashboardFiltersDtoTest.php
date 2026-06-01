<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Dashboard;

use App\Dto\Dashboard\DashboardFiltersDto;
use Tests\TestCase;

final class DashboardFiltersDtoTest extends TestCase
{
    public function test_dashboard_filters_dto_initializes_with_defaults(): void
    {
        $dto = new DashboardFiltersDto();

        $this->assertNull($dto->search);
        $this->assertNull($dto->genre);
        $this->assertNull($dto->author);
        $this->assertNull($dto->year);
        $this->assertNull($dto->status);
        $this->assertSame('rating', $dto->sort);
        $this->assertSame(18, $dto->perPage);
    }

    public function test_dashboard_filters_dto_initializes_with_custom_values(): void
    {
        $dto = new DashboardFiltersDto(
            'Laravel',
            5,
            12,
            2026,
            'active',
            'title',
            25
        );

        $this->assertSame('Laravel', $dto->search);
        $this->assertSame(5, $dto->genre);
        $this->assertSame(12, $dto->author);
        $this->assertSame(2026, $dto->year);
        $this->assertSame('active', $dto->status);
        $this->assertSame('title', $dto->sort);
        $this->assertSame(25, $dto->perPage);
    }
}
