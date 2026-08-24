<x-layouts::app.header title="{{ $book->title }}">
    <div class="flex min-h-screen flex-col">
        <div class="flex-1 flex flex-col gap-6 p-6">

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <flux:button href="{{ route('books.index') }}" variant="ghost" icon="arrow-left" />
                    <div>
                        <flux:heading size="xl">{{ $book->title }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-500">Book details</flux:text>
                    </div>
                </div>
                <flux:button href="{{ route('books.edit', $book->id) }}" variant="primary" icon="pencil">Edit</flux:button>
            </div>

            <div class="max-w-2xl">
                <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-700 dark:bg-zinc-900">

                    {{-- Cover --}}
                    @if ($book->coverImage)
                        <div class="h-56 overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                            <img src="{{ Storage::disk('s3')->url($book->coverImage) }}" alt="{{ $book->title }}"
                                 class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div class="px-6 py-4 flex justify-between">
                            <flux:text class="text-zinc-500 text-sm">Author</flux:text>
                            <flux:text class="text-sm font-medium">{{ $book->authorName ?? '—' }}</flux:text>
                        </div>
                        <div class="px-6 py-4 flex justify-between">
                            <flux:text class="text-zinc-500 text-sm">Price</flux:text>
                            <flux:text class="text-sm font-medium">${{ number_format($book->price, 2) }}</flux:text>
                        </div>
                        <div class="px-6 py-4 flex justify-between">
                            <flux:text class="text-zinc-500 text-sm">Stock</flux:text>
                            <flux:text class="text-sm font-medium">{{ $book->stock }}</flux:text>
                        </div>
                        <div class="px-6 py-4 flex justify-between">
                            <flux:text class="text-zinc-500 text-sm">Status</flux:text>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $book->status === 'active' ? 'bg-green-100 text-green-700' : ($book->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-zinc-100 text-zinc-600') }}">
                                {{ ucfirst($book->status) }}
                            </span>
                        </div>
                        <div class="px-6 py-4 flex justify-between">
                            <flux:text class="text-zinc-500 text-sm">Publish year</flux:text>
                            <flux:text class="text-sm font-medium">{{ $book->publishYear ?? '—' }}</flux:text>
                        </div>
                        <div class="px-6 py-4 flex justify-between">
                            <flux:text class="text-zinc-500 text-sm">Rating</flux:text>
                            <flux:text class="text-sm font-medium">{{ $book->averageRating }} ({{ $book->ratingsCount }} reviews)</flux:text>
                        </div>
                        @if (!empty($book->genres))
                            <div class="px-6 py-4 flex justify-between items-start">
                                <flux:text class="text-zinc-500 text-sm">Genres</flux:text>
                                <div class="flex flex-wrap gap-1 justify-end">
                                    @foreach ($book->genres as $genre)
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                            {{ $genre->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="px-6 py-4">
                            <flux:text class="text-zinc-500 text-sm mb-2">Description</flux:text>
                            <flux:text class="text-sm">{{ $book->description }}</flux:text>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layouts::app.header>
