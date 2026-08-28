<x-layouts::app.header title="Favorites">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-5xl mx-auto py-6">

            <div class="flex items-center gap-3 mb-6">
                <h1 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">My Favorites</h1>
                @if ($books !== null)
                    <span class="text-sm text-zinc-400">{{ $books->total }} {{ Str::plural('book', $books->total) }}</span>
                @endif
            </div>

            @if ($books === null || empty($books->items))
                <div class="flex flex-col items-center justify-center py-24 text-zinc-400">
                    <flux:icon name="heart" class="w-12 h-12 mb-3" />
                    <p class="text-sm">No favorites yet</p>
                    <a href="{{ route('dashboard') }}" class="mt-3 text-sm text-blue-500 hover:underline">Browse books</a>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    @foreach ($books->items as $book)
                        <div class="group flex flex-col rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5">

                            <a href="{{ route('catalog.show', $book->slug) }}" class="flex flex-col flex-1">

                                {{-- Cover --}}
                                <div class="relative aspect-[2/3] bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                    @if ($book->coverImage)
                                        <img src="{{ Storage::disk('s3')->url($book->coverImage) }}"
                                             alt="{{ $book->title }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-zinc-200 dark:bg-zinc-700">
                                            <flux:icon name="book-open" class="w-10 h-10 text-zinc-400" />
                                        </div>
                                    @endif

                                    @if ($book->status === 'draft')
                                        <span class="absolute top-2 left-2 text-xs bg-amber-500 text-white px-2 py-0.5 rounded-full font-medium">
                                            Draft
                                        </span>
                                    @elseif ($book->status === 'archived')
                                        <span class="absolute top-2 left-2 text-xs bg-zinc-500 text-white px-2 py-0.5 rounded-full font-medium">
                                            Archived
                                        </span>
                                    @elseif ($book->stock > 0)
                                        <span class="absolute top-2 left-2 text-xs bg-green-500 text-white px-2 py-0.5 rounded-full font-medium">
                                            In stock
                                        </span>
                                    @else
                                        <span class="absolute top-2 left-2 text-xs bg-red-500 text-white px-2 py-0.5 rounded-full font-medium">
                                            Out of stock
                                        </span>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="p-3 flex flex-col gap-1 flex-1">
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 truncate">{{ $book->authorName ?? '—' }}</p>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 leading-tight line-clamp-2">{{ $book->title }}</p>

                                    @if ($book->averageRating > 0)
                                        <div class="flex items-center gap-1 mt-auto pt-1">
                                            <flux:icon name="star" class="w-3 h-3 text-yellow-400" />
                                            <span class="text-xs text-zinc-500">{{ number_format($book->averageRating, 1) }} ({{ $book->ratingsCount }})</span>
                                        </div>
                                    @endif

                                    <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                                        ${{ number_format($book->price, 2) }}
                                    </p>
                                </div>

                            </a>

                            {{-- Remove from favorites + Add to cart --}}
                            <div class="px-3 pb-3 flex gap-2">

                                <form action="{{ route('favorites.toggle') }}" method="POST" class="shrink-0">
                                    @csrf
                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                    <button type="submit"
                                            title="Remove from favorites"
                                            class="p-1.5 rounded-lg border transition-colors border-red-300 bg-red-50 text-red-500 hover:bg-red-100 dark:border-red-800 dark:bg-red-950 dark:text-red-400">
                                        <flux:icon name="heart" class="w-3.5 h-3.5 fill-current" />
                                    </button>
                                </form>

                                <form action="{{ route('cart.store') }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit"
                                            @disabled($book->status !== 'active' || $book->stock === 0)
                                            title="{{ $book->status !== 'active' || $book->stock === 0 ? 'Out of stock' : 'Add to cart' }}"
                                            class="w-full py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium transition-colors flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600">
                                        <flux:icon name="shopping-cart" class="w-3.5 h-3.5" />
                                        Add to cart
                                    </button>
                                </form>

                            </div>

                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($books->lastPage > 1)
                    @php
                        $query = request()->query();
                        $current = $books->currentPage;
                        $last = $books->lastPage;
                    @endphp
                    <div class="mt-8 flex items-center justify-between">
                        <p class="text-sm text-zinc-500">
                            Showing {{ $books->perPage * ($current - 1) + 1 }}–{{ min($books->perPage * $current, $books->total) }}
                            of {{ $books->total }}
                        </p>
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
            @endif

        </div>
    </div>
</x-layouts::app.header>
