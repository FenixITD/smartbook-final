<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Traits\CreatesPaginatedResponse;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractEloquentRepository
{
    use CreatesPaginatedResponse;

    abstract protected function getModelClass(): string;
    abstract protected function getResponseDtoClass(): string;

    protected function query(): Builder
    {
        return app($this->getModelClass())->newQuery();
    }

    protected function mapToDto(mixed $model): mixed
    {
        if ($model === null) {
            return null;
        }

        return call_user_func([$this->getResponseDtoClass(), 'fromModel'], $model);
    }

    protected function executeGetById(int $id): mixed
    {
        $model = $this->query()->find($id);

        return $this->mapToDto($model);
    }

    protected function executeCreate(mixed $data): mixed
    {
        $model = $this->query()->create($data->toArray());

        $this->afterCreate($model);

        return $this->mapToDto($model->fresh());
    }

    protected function executeUpdate(int $id, mixed $data): mixed
    {
        $model = $this->query()->findOrFail($id);

        $model->update($data->toArray());

        $this->afterUpdate($model);

        return $this->mapToDto($model->fresh());
    }

    public function delete(int $id): bool
    {
        $deleted = (bool) $this->query()->findOrFail($id)->delete();

        if ($deleted) {
            $this->afterDelete($id);
        }

        return $deleted;
    }

    protected function afterCreate(mixed $model): void {}
    protected function afterUpdate(mixed $model): void {}
    protected function afterDelete(int $id): void {}
}
