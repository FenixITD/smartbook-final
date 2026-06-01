<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Favorites;

use App\Dto\Favorite\FavoriteFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Http\Controllers\Web\Favorites\ShowFavoritesController;
use App\Models\User;
use App\Services\Favorite\FavoriteService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class ShowFavoritesControllerTest extends TestCase
{
    private MockInterface&FavoriteService $favoriteService;
    private ShowFavoritesController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->favoriteService = Mockery::mock(FavoriteService::class);
        $this->app->instance(FavoriteService::class, $this->favoriteService);

        $this->controller = $this->app->make(ShowFavoritesController::class);
    }

    public function test_returns_view_with_favorite_books(): void
    {
        $user = new User();
        $user->id = 5;

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('user')->once()->andReturn($user);

        $paginated = Mockery::mock(PaginatedResponseDto::class);

        $this->favoriteService
            ->shouldReceive('getBooksByUser')
            ->once()
            ->with(
                5,
                Mockery::on(fn (FavoriteFiltersDto $filters) => $filters->perPage === 18)
            )
            ->andReturn($paginated);

        $response = ($this->controller)($request);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('favorites.index', $response->name());

        $data = $response->getData();
        $this->assertArrayHasKey('books', $data);
        $this->assertSame($paginated, $data['books']);
    }
}
