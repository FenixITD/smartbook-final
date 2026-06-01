<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\SendEntityNotificationJob;
use Illuminate\Database\Eloquent\Model;

abstract class BaseEntityObserver
{
    /**
     * @var array<int, string>
     */
    protected array $hidden = ['password', 'remember_token'];

    public function created(Model $model): void
    {
        $this->dispatch($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->dispatch($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->dispatch($model, 'deleted');
    }

    private function dispatch(Model $model, string $action): void
    {
        /** @var array<string, mixed> $data */
        $data = collect($model->getAttributes())
            ->except($this->hidden)
            ->toArray();

        SendEntityNotificationJob::dispatch(
            entityType: class_basename($model),
            action: $action,
            entityData: $data,
            performedAt: now()->format('d.m.Y H:i:s'),
        );
    }
}
