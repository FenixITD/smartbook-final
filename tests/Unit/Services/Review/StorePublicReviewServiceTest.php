<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Review;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewResponseDto;
use App\Jobs\SendEntityNotificationJob;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\Review\StorePublicReviewService;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class StorePublicReviewServiceTest extends TestCase
{
    private ReviewRepositoryInterface&MockInterface $repository;
    private StorePublicReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->service = new StorePublicReviewService($this->repository);
        Queue::fake();
    }

    public function test_executes_creation_and_dispatches_job(): void
    {
        $dto = new ReviewDto(1, 2, 5.0, 'Comment');
        $responseDto = new ReviewResponseDto(1, 1, 2, 'Name', 5.0, 'Comment', 'date', 'date');

        $this->repository->expects('create')->with($dto)->andReturn($responseDto);

        $this->service->execute($dto);

        Queue::assertPushed(SendEntityNotificationJob::class, function (SendEntityNotificationJob $job) {
            $entityType = (fn() => $this->entityType)->call($job);
            $action = (fn() => $this->action)->call($job);
            $entityData = (fn() => $this->entityData)->call($job);

            return $entityType === 'Review'
                && $action === 'created'
                && $entityData['id'] === 1
                && $entityData['user_name'] === 'Name'
                && $entityData['rating'] === 5.0
                && $entityData['comment'] === 'Comment';
        });
    }
}
