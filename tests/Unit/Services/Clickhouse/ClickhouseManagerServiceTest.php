<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Clickhouse;

use App\Services\Clickhouse\ClickhouseManagerService;
use ClickHouseDB\Client;
use ClickHouseDB\Statement;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use Tests\TestCase;

final class ClickhouseManagerServiceTest extends TestCase
{
    private Client&MockInterface $client;
    private ClickhouseManagerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Mockery::mock(Client::class);
        $this->service = new ClickhouseManagerService();

        $reflection = new ReflectionClass($this->service);
        $prop = $reflection->getProperty('client');
        $prop->setValue($this->service, $this->client);
    }

    public function test_insert_calls_client_insert(): void
    {
        $this->client->expects('insert')->with('logs', [['val']], ['msg']);

        $this->service->insert('logs', ['msg' => 'val']);
    }

    public function test_insert_batch_with_empty_rows_does_nothing(): void
    {
        $this->client->expects('insert')->never();

        $this->service->insertBatch('logs', []);
    }

    public function test_insert_batch_with_empty_columns_does_nothing(): void
    {
        $this->client->expects('insert')->never();

        $this->service->insertBatch('logs', [[]]);
    }

    public function test_insert_batch_calls_transport_write(): void
    {
        $transport = Mockery::mock();
        $transport->expects('write')->once()->andReturnUsing(function (string $sql) {
            $this->assertStringContainsString('INSERT INTO `logs`', $sql);
            $this->assertStringContainsString("'hello'", $sql);
        });
        $this->client->expects('transport')->andReturn($transport);

        $this->service->insertBatch('logs', [['msg' => 'hello']]);
    }

    public function test_select_returns_rows(): void
    {
        $statement = Mockery::mock(Statement::class);
        $statement->expects('rows')->andReturn([['id' => 1]]);
        $this->client->expects('select')->with('SELECT 1', [])->andReturn($statement);

        $result = $this->service->select('SELECT 1');

        $this->assertSame([['id' => 1]], $result);
    }

    public function test_count_returns_count_from_first_row(): void
    {
        $statement = Mockery::mock(Statement::class);
        $statement->expects('rows')->andReturn([['count' => '7']]);
        $this->client->expects('select')->andReturn($statement);

        $this->assertSame(7, $this->service->count('SELECT count() as count'));
    }

    public function test_count_returns_zero_when_no_count_key(): void
    {
        $statement = Mockery::mock(Statement::class);
        $statement->expects('rows')->andReturn([['other' => 1]]);
        $this->client->expects('select')->andReturn($statement);

        $this->assertSame(0, $this->service->count('SELECT 1'));
    }

    public function test_execute_calls_client_write(): void
    {
        $this->client->expects('write')->with('DROP TABLE test');

        $this->service->execute('DROP TABLE test');
    }

    public function test_ping_delegates_to_client(): void
    {
        $this->client->expects('ping')->andReturn(true);

        $this->assertTrue($this->service->ping());
    }
}
