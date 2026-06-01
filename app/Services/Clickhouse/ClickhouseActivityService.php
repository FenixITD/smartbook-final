<?php

declare(strict_types=1);

namespace App\Services\Clickhouse;

use App\Models\ClickhouseActivity;
use App\Models\User;
use Illuminate\Support\Collection;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

class ClickhouseActivityService
{
    /**
     * @return array<string, mixed>
     */
    public function buildRow(ClickhouseActivity $activity): array
    {
        $id = $this->generateId();
        $now = now()->toDateTimeString();

        return [
            'id' => $id,
            'log_name' => $activity->log_name ?? 'default',
            'description' => $activity->description ?? '',
            'subject_type' => $activity->subject_type ?? '',
            'subject_id' => $activity->subject_id !== null ? $activity->subject_id : null,
            'causer_type' => $activity->causer_type ?? '',
            'causer_id' => $activity->causer_id !== null ? $activity->causer_id : null,
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
            /** @var User|null $causer */
            $causer = $activity->causer;
            return $causer->name ?? '';
        }

        if ($activity->causer_id === null) {
            return '';
        }

        /** @var User|null $user */
        $user = User::find($activity->causer_id);

        return $user->name ?? '';
    }

    private function serializeProperties(ClickhouseActivity $activity): string
    {
        $props = $activity->properties;

        $array = $props instanceof Collection ? $props->toArray() : [];

        return json_encode($array, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
