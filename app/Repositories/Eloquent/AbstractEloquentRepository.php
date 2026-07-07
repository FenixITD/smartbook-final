<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Traits\CreatesPaginatedResponse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 * @template TDto
 */
abstract class AbstractEloquentRepository
{
    use CreatesPaginatedResponse;

    /** @return class-string<TModel> */
    abstract protected function getModelClass(): string;

    /** @return class-string<TDto> */
    abstract protected function getResponseDtoClass(): string;

    /** @return Builder<TModel> */
    protected function query(): Builder
    {
        /** @var TModel $model */
        $model = app($this->getModelClass());

        /** @phpstan-ignore-next-line */
        return $model->newQuery();
    }

    /**
     * @param TModel|null $model
     * @return TDto|null
     */
    protected function mapToDto(mixed $model): mixed
    {
        if ($model === null) {
            return null;
        }

        $dtoClass = $this->getResponseDtoClass();

        return $dtoClass::fromModel($model);
    }

    /** @return TDto|null */
    protected function executeGetById(int $id): mixed
    {
        $model = $this->query()->find($id);

        return $this->mapToDto($model);
    }

    /** @param Arrayable<string, mixed> $data */
    protected function executeCreate(Arrayable $data): mixed
    {
        /** @var array<string, mixed> $attributes */
        $attributes = $data->toArray();

        /** @var TModel $model */
        $model = $this->query()->create($attributes);

        $this->afterCreate($model);

        /** @var TDto $dto */
        $dto = $this->mapToDto($model->fresh());

        return $dto;
    }

    /** @param Arrayable<string, mixed> $data */
    protected function executeUpdate(int $id, Arrayable $data): mixed
    {
        /** @var array<string, mixed> $attributes */
        $attributes = $data->toArray();

        /** @var TModel $model */
        $model = $this->query()->findOrFail($id);
        $model->update($attributes);

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

    /** @param TModel $model */
    protected function afterCreate(mixed $model): void {}

    /** @param TModel $model */
    protected function afterUpdate(mixed $model): void {}

    protected function afterDelete(int $id): void {}
}
