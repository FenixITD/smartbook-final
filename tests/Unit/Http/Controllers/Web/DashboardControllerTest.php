<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web;

use App\Dto\Dashboard\DashboardFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Requests\Dashboard\DashboardListRequest;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Book\GetDashboardBooksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class DashboardControllerTest extends TestCase
{
    private MockInterface&GetDashboardBooksService $dashboardBooksService;
    private MockInterface&GenreRepositoryInterface $genreRepository;
    private MockInterface&FavoriteRepositoryInterface $favoriteRepository;
    private DashboardController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dashboardBooksService = Mockery::mock(GetDashboardBooksService::class);
        $this->genreRepository = Mockery::mock(GenreRepositoryInterface::class);
        $this->favoriteRepository = Mockery::mock(FavoriteRepositoryInterface::class);

        $this->app->instance(GetDashboardBooksService::class, $this->dashboardBooksService);
        $this->app->instance(GenreRepositoryInterface::class, $this->genreRepository);
        $this->app->instance(FavoriteRepositoryInterface::class, $this->favoriteRepository);

        $this->controller = $this->app->make(DashboardController::class);

        Log::spy();
    }

    public function test_returns_view_with_data_for_guest_user(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);
        Auth::shouldReceive('id')->never();

        $paginated = Mockery::mock(PaginatedResponseDto::class);

        $this->dashboardBooksService->shouldReceive('get')->once()->andReturn($paginated);
        $this->genreRepository->shouldReceive('getAll')->once()->andReturn(['Genre 1', 'Genre 2']);
        $this->favoriteRepository->shouldNotReceive('getBookIdsByUser');

        $response = ($this->controller)($this->makeRequest());

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('dashboard', $response->name());

        $data = $response->getData();
        $this->assertSame($paginated, $data['paginated']);
        $this->assertSame(['Genre 1', 'Genre 2'], $data['genres']);
        $this->assertSame([], $data['favoriteBookIds']);
    }

    public function test_returns_view_with_data_for_authenticated_user(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('id')->once()->andReturn(42);

        $paginated = Mockery::mock(PaginatedResponseDto::class);

        $this->dashboardBooksService->shouldReceive('get')->once()->andReturn($paginated);
        $this->genreRepository->shouldReceive('getAll')->once()->andReturn(['Genre 1']);
        $this->favoriteRepository->shouldReceive('getBookIdsByUser')->once()->with(42)->andReturn([10, 15, 20]);

        $response = ($this->controller)($this->makeRequest());

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('dashboard', $response->name());

        $data = $response->getData();
        $this->assertSame($paginated, $data['paginated']);
        $this->assertSame(['Genre 1'], $data['genres']);
        $this->assertSame([10, 15, 20], $data['favoriteBookIds']);
    }

    public function test_passes_dto_from_request_to_service(): void
    {
        Auth::shouldReceive('check')->andReturn(false);

        $this->dashboardBooksService
            ->shouldReceive('get')
            ->once()
            ->with(Mockery::on(function (DashboardFiltersDto $dto) {
                return $dto->search === 'harry'
                    && $dto->genre === 2
                    && $dto->author === 3
                    && $dto->year === 2005
                    && $dto->status === 'active'
                    && $dto->sort === 'price_asc';
            }))
            ->andReturn(Mockery::mock(PaginatedResponseDto::class));

        $this->genreRepository->shouldReceive('getAll')->andReturn([]);

        ($this->controller)($this->makeRequest([
            'search' => 'harry',
            'genre' => 2,
            'author' => 3,
            'year' => 2005,
            'status' => 'active',
            'sort' => 'price_asc',
        ]));
    }

    private function makeRequest(array $data = []): DashboardListRequest
    {
        return DashboardListRequest::createFrom(
            Request::create('/dashboard', 'GET', $data)
        );
    }
}
