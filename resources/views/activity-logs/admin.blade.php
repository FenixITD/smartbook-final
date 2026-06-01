<x-layouts::app.header title="Activity Logs">
    <div class="flex min-h-screen flex-col">
        <div class="flex-1 flex flex-col gap-6 p-6">

            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">Activity Logs</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">All tracked actions across the system</flux:text>
                </div>
            </div>

            {{-- Filters --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <form method="GET" action="{{ route('activity-logs.index') }}" class="flex flex-wrap gap-3 items-end">

                    <div class="w-48">
                        <flux:label for="subjectType">Model</flux:label>
                        <flux:select id="subjectType" name="subjectType">
                            <flux:select.option value="">All models</flux:select.option>
                            @foreach ($subjectTypes as $type)
                                <flux:select.option value="{{ $type }}" :selected="request('subjectType') === $type">
                                    {{ $type }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="w-48">
                        <flux:label for="logName">Log name</flux:label>
                        <flux:select id="logName" name="logName">
                            <flux:select.option value="">All</flux:select.option>
                            @foreach ($subjectTypes as $type)
                                <flux:select.option value="{{ $type }}" :selected="request('logName') === $type">
                                    {{ $type }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="w-32">
                        <flux:label for="perPage">Per page</flux:label>
                        <flux:select id="perPage" name="perPage">
                            @foreach ([20, 50, 100] as $pp)
                                <flux:select.option value="{{ $pp }}" :selected="request('perPage', 20) == $pp">
                                    {{ $pp }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <flux:button type="submit" variant="filled">Apply</flux:button>

                    @if (request('subjectType') || request('logName') || request('perPage'))
                        <flux:button href="{{ route('activity-logs.index') }}" variant="ghost">Reset</flux:button>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-700 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider w-12">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Model</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Changes</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Causer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Date</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($logs->items as $item)
                        @php
                            // Универсальное приведение к массиву для безопасного извлечения данных
                            $log = is_object($item) ? (method_exists($item, 'toArray') ? $item->toArray() : (array) $item) : $item;

                            // Извлекаем ключи с поддержкой как DTO (camelCase), так и сырых данных БД (snake_case)
                            $id = $log['id'] ?? null;
                            $description = $log['description'] ?? '';
                            $logName = $log['logName'] ?? $log['log_name'] ?? null;
                            $subjectType = $log['subjectType'] ?? $log['subject_type'] ?? null;
                            $subjectId = $log['subjectId'] ?? $log['subject_id'] ?? null;
                            $causerName = $log['causerName'] ?? $log['causer_name'] ?? null;
                            $causerId = $log['causerId'] ?? $log['causer_id'] ?? null;
                            $createdAt = $log['createdAt'] ?? $log['created_at'] ?? null;

                            $eventColor = match($description) {
                                'created' => 'bg-green-100 text-green-700',
                                'updated' => 'bg-blue-100 text-blue-700',
                                'deleted' => 'bg-red-100 text-red-700',
                                default   => 'bg-zinc-100 text-zinc-600',
                            };

                            // ClickHouse может отдавать properties в виде JSON-строки
                            $props = $log['properties'] ?? [];
                            if (is_string($props)) {
                                $props = json_decode($props, true) ?? [];
                            } elseif (is_object($props) && method_exists($props, 'toArray')) {
                                $props = $props->toArray();
                            } else {
                                $props = (array) $props;
                            }

                            $attributes = $props['attributes'] ?? array_diff_key($props, array_flip(['old', 'attributes']));
                            $old = $props['old'] ?? [];
                        @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors align-top">
                            <td class="px-4 py-3 text-sm text-zinc-400">{{ $id }}</td>

                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $eventColor }}">
                                    {{ ucfirst($description) }}
                                </span>
                                @if ($logName)
                                    <p class="text-xs text-zinc-400 mt-1">{{ $logName }}</p>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $subjectType ?? '—' }}
                                </p>
                                @if ($subjectId)
                                    <p class="text-xs text-zinc-400">ID: {{ $subjectId }}</p>
                                @endif
                            </td>

                            <td class="px-4 py-3 max-w-xs">
                                @if (!empty($attributes))
                                    <div class="flex flex-col gap-1">
                                        @foreach ($attributes as $field => $newValue)
                                            <div class="text-xs">
                                                <span class="font-medium text-zinc-600 dark:text-zinc-400">{{ $field }}:</span>
                                                @if (isset($old[$field]) && $old[$field] !== $newValue)
                                                    <span class="line-through text-red-400 ml-1">
                                                        {{ is_array($old[$field]) ? json_encode($old[$field]) : $old[$field] }}
                                                    </span>
                                                    <span class="text-green-600 ml-1">
                                                        {{ is_array($newValue) ? json_encode($newValue) : $newValue }}
                                                    </span>
                                                @else
                                                    <span class="text-zinc-700 dark:text-zinc-300 ml-1">
                                                        {{ is_array($newValue) ? json_encode($newValue) : $newValue }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $causerName ?? 'System' }}
                                @if ($causerId)
                                    <p class="text-xs text-zinc-400">ID: {{ $causerId }}</p>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-zinc-500 whitespace-nowrap">
                                {{ $createdAt }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-sm text-zinc-400">
                                No activity logs found
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                @if ($logs->lastPage > 1)
                    <div class="border-t border-zinc-200 dark:border-zinc-700 px-6 py-4">
                        {!! $logs->links !!}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-layouts::app.header>
