<x-layouts::app.sidebar title="Review">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <flux:button href="{{ route('reviews.index') }}" variant="ghost" icon="arrow-left" />
                <div>
                    <flux:heading size="xl">Review #{{ $review->id }}</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Review details</flux:text>
                </div>
            </div>
            <flux:button href="{{ route('reviews.edit', $review) }}" variant="primary" icon="pencil">Edit</flux:button>
        </div>

        <div class="max-w-xl">
            <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-700 dark:bg-zinc-900">

                {{-- Header --}}
                <div class="bg-gradient-to-br from-yellow-50 to-zinc-100 dark:from-yellow-950 dark:to-zinc-800 px-6 py-6 flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-yellow-500 text-white text-xl font-bold shadow-md">
                        {{ number_format($review->rating, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <flux:icon name="star" class="w-4 h-4 {{ $i <= round($review->rating) ? 'text-yellow-400' : 'text-zinc-300' }}" />
                            @endfor
                        </div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                            {{ $review->user?->name ?? 'User #'.$review->user_id }}
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <div class="px-6 py-4 flex justify-between items-center">
                        <flux:text class="text-zinc-500 text-sm">Book</flux:text>
                        <flux:text class="text-sm font-medium">{{ $review->book?->title ?? 'Book #'.$review->book_id }}</flux:text>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <flux:text class="text-zinc-500 text-sm">Rating</flux:text>
                        <flux:text class="text-sm font-medium">{{ number_format($review->rating, 1) }} / 5</flux:text>
                    </div>
                    <div class="px-6 py-4">
                        <flux:text class="text-zinc-500 text-sm mb-2">Comment</flux:text>
                        <flux:text class="text-sm">{{ $review->comment ?? '—' }}</flux:text>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <flux:text class="text-zinc-500 text-sm">Created at</flux:text>
                        <flux:text class="text-sm font-medium">{{ $review->created_at->format('d.m.Y H:i') }}</flux:text>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <flux:text class="text-zinc-500 text-sm">Updated at</flux:text>
                        <flux:text class="text-sm font-medium">{{ $review->updated_at->format('d.m.Y H:i') }}</flux:text>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts::app.sidebar>
