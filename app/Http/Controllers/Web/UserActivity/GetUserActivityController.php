<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\UserActivity;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Http\Requests\UserActivity\UserActivityFilterRequest;
use App\Services\User\UserActivityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final readonly class GetUserActivityController
{
    public function __construct(
        private UserActivityService $service,
    ) {
    }

    public function __invoke(UserActivityFilterRequest $request): View
    {
        $filters = new ActivityLogFiltersDto(
            perPage: $request->integer('perPage', 15),
            causerId: (int) Auth::id(),
            logNames: ['CartItem', 'Favorite', 'Review'],
        );

        [$logs, $booksById] = $this->service->fetchWithBooks($filters);

        return view('user-activity.index', compact('logs', 'booksById'));
    }
}
