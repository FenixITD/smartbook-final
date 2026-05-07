<x-layouts::app.header title="Author">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <flux:button href="{{ route('authors.index') }}" variant="ghost" icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ $author->name }}</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Author details</flux:text>
                </div>
            </div>
            <flux:button href="{{ route('authors.edit', $author) }}" variant="primary" icon="pencil">
                Edit
            </flux:button>
        </div>

        {{-- Info card --}}
        <div class="max-w-xl">
            <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-700 dark:bg-zinc-900">

                {{-- Avatar header --}}
                <div class="bg-linear-to-br from-blue-50 to-zinc-100 dark:from-blue-950 dark:to-zinc-800 px-6 py-8 flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-600 text-white text-2xl font-bold shadow-md">
                        {{ mb_strtoupper(mb_substr($author->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $author->name }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">ID: {{ $author->id }}</div>
                    </div>
                </div>

                {{-- Details --}}
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <div class="px-6 py-4 flex justify-between items-center">
                        <flux:text class="text-zinc-500 text-sm">Created at</flux:text>
                        <flux:text class="text-sm font-medium">{{ $author->created_at->format('d.m.Y H:i') }}</flux:text>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <flux:text class="text-zinc-500 text-sm">Updated at</flux:text>
                        <flux:text class="text-sm font-medium">{{ $author->updated_at->format('d.m.Y H:i') }}</flux:text>
                    </div>
                    @if ($author->books_count ?? $author->books?->count())
                        <div class="px-6 py-4 flex justify-between items-center">
                            <flux:text class="text-zinc-500 text-sm">Books count</flux:text>
                            <flux:badge variant="solid">{{ $author->books_count ?? $author->books->count() }}</flux:badge>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-layouts::app.header>
