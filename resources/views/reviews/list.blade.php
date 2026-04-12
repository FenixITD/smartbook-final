<x-layouts::app.header title="Reviews">
    <div class="flex min-h-screen flex-col">

        <div class="flex-1 flex flex-col gap-6 p-6">

            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">Reviews</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Manage book reviews</flux:text>
                </div>
                <flux:button href="{{ route('reviews.create') }}" variant="primary" icon="plus">
                    Add review
                </flux:button>
            </div>

            @if (session('success'))
                <flux:callout variant="success" icon="check-circle">
                    {{ session('success') }}
                </flux:callout>
            @endif

            {{-- Filters --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <form method="GET" action="{{ route('reviews.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-48">
                        <flux:input
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by ID..."
                            icon="magnifying-glass"
                        />
                    </div>
                    <div class="w-40">
                        <flux:select name="sortBy">
                            <flux:select.option value="id" :selected="request('sortBy') === 'id'">By ID</flux:select.option>
                            <flux:select.option value="rating" :selected="request('sortBy') === 'rating'">By rating</flux:select.option>
                            <flux:select.option value="created_at" :selected="request('sortBy') === 'created_at'">By date</flux:select.option>
                        </flux:select>
                    </div>
                    <div class="w-40">
                        <flux:select name="sortDirection">
                            <flux:select.option value="asc" :selected="request('sortDirection') === 'asc'">Ascending</flux:select.option>
                            <flux:select.option value="desc" :selected="request('sortDirection') === 'desc'">Descending</flux:select.option>
                        </flux:select>
                    </div>
                    <flux:button type="submit" variant="filled">Apply</flux:button>
                    @if(request('search') || request('sortBy'))
                        <flux:button href="{{ route('reviews.index') }}" variant="ghost">Reset</flux:button>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-700 dark:bg-zinc-900 flex-1 flex flex-col">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-12">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Book</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Comment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Created at</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($paginated->items as $review)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                <td class="px-6 py-4 text-sm text-zinc-400 dark:text-zinc-500">{{ $review->id }}</td>
                                <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $review->user?->name ?? 'User #'.$review->user_id }}
                                </td>
                                <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400 max-w-xs truncate">
                                    {{ $review->book?->title ?? 'Book #'.$review->book_id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <flux:icon name="star" class="w-4 h-4 text-yellow-400" />
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ number_format($review->rating, 1) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400 max-w-xs truncate">
                                    {{ $review->comment ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $review->created_at->format('d.m.Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <flux:button href="{{ route('reviews.show', $review) }}" variant="ghost" size="sm" icon="eye">View</flux:button>
                                        <flux:button href="{{ route('reviews.edit', $review) }}" variant="ghost" size="sm" icon="pencil">Edit</flux:button>
                                        <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Delete review #{{ $review->id }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" variant="ghost" size="sm" icon="trash">Delete</flux:button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-zinc-400">
                                        <flux:icon name="chat-bubble-left-right" class="w-10 h-10" />
                                        <span class="text-sm">No reviews yet</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($paginated->lastPage > 1)
                    @php
                        $query = request()->query();
                        $current = $paginated->currentPage;
                        $last = $paginated->lastPage;
                    @endphp
                    <div class="border-t border-zinc-200 dark:border-zinc-700 px-6 py-4 mt-auto flex items-center justify-between">
                        <p class="text-sm text-zinc-500">Page {{ $current }} of {{ $last }}</p>
                        <div class="flex items-center gap-1">
                            @if ($current > 1)
                                <a href="{{ request()->fullUrlWithQuery(array_merge($query, ['page' => $current - 1])) }}"
                                   class="px-3 py-1.5 text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">‹</a>
                            @endif
                            @for ($page = max(1, $current - 2); $page <= min($last, $current + 2); $page++)
                                <a href="{{ request()->fullUrlWithQuery(array_merge($query, ['page' => $page])) }}"
                                   class="px-3 py-1.5 text-sm rounded-lg border transition-colors
                                          {{ $page === $current ? 'border-blue-600 bg-blue-600 text-white' : 'border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800' }}">
                                    {{ $page }}
                                </a>
                            @endfor
                            @if ($current < $last)
                                <a href="{{ request()->fullUrlWithQuery(array_merge($query, ['page' => $current + 1])) }}"
                                   class="px-3 py-1.5 text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">›</a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <flux:text class="text-zinc-400 text-sm">
                Total reviews: {{ $paginated->total }}
            </flux:text>

        </div>
    </div>
</x-layouts::app.header>
