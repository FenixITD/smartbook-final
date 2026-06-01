<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Favorites;

use App\Http\Controllers\Web\Favorites\ToggleFavoriteController;
use App\Http\Requests\Favorite\FavoriteToggleWebRequest;
use App\Models\User;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class ToggleFavoriteControllerTest extends TestCase
{
    private MockInterface&FavoriteRepositoryInterface $repository;
    private ToggleFavoriteController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(FavoriteRepositoryInterface::class);
        $this->app->instance(FavoriteRepositoryInterface::class, $this->repository);

        $this->controller = $this->app->make(ToggleFavoriteController::class);

        $this->app->make('session')->start();
    }

    public function test_toggles_favorite_and_redirects_back(): void
    {
        $user = new User();
        $user->id = 7;

        $request = FavoriteToggleWebRequest::createFrom(
            Request::create('/favorites/toggle', 'POST', ['book_id' => 42])
        );
        $request->setUserResolver(fn () => $user);

        $this->repository
            ->shouldReceive('toggle')
            ->once()
            ->with(7, 42)
            ->andReturn(true);

        $response = ($this->controller)($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
