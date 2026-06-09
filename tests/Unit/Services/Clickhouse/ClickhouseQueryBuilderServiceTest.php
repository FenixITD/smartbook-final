<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Clickhouse;

use App\Services\Clickhouse\ClickhouseManagerService;
use App\Services\Clickhouse\ClickhouseQueryBuilderService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ClickhouseQueryBuilderServiceTest extends TestCase
{
    private ClickhouseManagerService&MockInterface $manager;
    private ClickhouseQueryBuilderService $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = Mockery::mock(ClickhouseManagerService::class);
        $this->builder = new ClickhouseQueryBuilderService($this->manager, 'test_table');
    }

    public function test_count_without_conditions(): void
    {
        $this->manager->expects('count')->with("SELECT count() as count FROM test_table", [])->andReturn(5);

        $this->assertSame(5, $this->builder->count());
    }

    public function test_get_without_conditions(): void
    {
        $this->manager->expects('select')->with("SELECT * FROM test_table ORDER BY created_at DESC", [])->andReturn([['id' => 1]]);

        $this->assertSame([['id' => 1]], $this->builder->get());
    }

    public function test_where_adds_condition(): void
    {
        $this->manager->expects('select')->with("SELECT * FROM test_table WHERE col = {col_0:String} ORDER BY created_at DESC", ['col_0' => 'val'])->andReturn([]);

        $this->builder->where('col', 'val')->get();
    }

    public function test_where_adds_int_condition(): void
    {
        $this->manager->expects('select')->with("SELECT * FROM test_table WHERE col = {col_0:UInt64} ORDER BY created_at DESC", ['col_0' => 123])->andReturn([]);

        $this->builder->where('col', 123)->get();
    }

    public function test_where_in_adds_condition(): void
    {
        $this->manager->expects('select')->with("SELECT * FROM test_table WHERE col IN ('a','b') ORDER BY created_at DESC", [])->andReturn([]);

        $this->builder->whereIn('col', ['a', 'b'])->get();
    }

    public function test_order_by_desc_changes_order(): void
    {
        $this->manager->expects('select')->with("SELECT * FROM test_table ORDER BY custom_col DESC", [])->andReturn([]);

        $this->builder->orderByDesc('custom_col')->get();
    }

    public function test_limit_and_offset(): void
    {
        $this->manager->expects('select')->with("SELECT * FROM test_table ORDER BY created_at DESC LIMIT 10 OFFSET 5", [])->andReturn([]);

        $this->builder->limit(10)->offset(5)->get();
    }
}
