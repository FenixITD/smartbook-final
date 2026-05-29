<?php

declare(strict_types=1);

namespace App\Services\Clickhouse;

use App\Models\ClickhouseActivity;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class ClickhouseActivityService
{
    public function buildRow(ClickhouseActivity $activity): array
    {
        $id  = $this->generateId();
        $now = now()->toDateTimeString();

        return [
            'id' => $id,
            'log_name' => $activity->log_name ?? 'default',
            'description' => $activity->description ?? '',
            'subject_type' => $activity->subject_type ?? '',
            'subject_id' => $activity->subject_id ? (int) $activity->subject_id : null,
            'causer_type' => $activity->causer_type ?? '',
            'causer_id' => $activity->causer_id ? (int) $activity->causer_id : null,
            'causer_name' => $this->resolveCauserName($activity),
            'properties' => $this->serializeProperties($activity),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function generateId(): int
    {
        return (int) (microtime(true) * 1000) * 1000 + random_int(0, 999);
    }

    private function resolveCauserName(ClickhouseActivity $activity): string
    {
        if ($activity->relationLoaded('causer')) {
            return $activity->causer?->name ?? '';
        }

        if ($activity->causer_id === null) {
            return '';
        }

        return User::find($activity->causer_id)?->name ?? '';
    }

    private function serializeProperties(ClickhouseActivity $activity): string
    {
        $props = $activity->properties;

        $array = match (true) {
            $props instanceof Collection => $props->toArray(),
            is_array($props)            => $props,
            default                     => [],
        };

        return json_encode($array, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
