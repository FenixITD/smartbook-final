<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Reviews;

use App\Http\Controllers\Web\Reviews\DeleteReviewController;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class DeleteReviewControllerTest extends TestCase
{
    private MockInterface&ReviewRepositoryInterface $repository;
    private DeleteReviewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->app->instance(ReviewRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(DeleteReviewController::class);
    }

    public function test_calls_repository_delete_and_redirects(): void
    {
        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with(10)
            ->andReturn(true);

        $response = ($this->controller)(10);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('reviews.index'), $response->getTargetUrl());
    }
}
