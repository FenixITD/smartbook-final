<x-layouts::app.header title="Dashboard">
    <div class="flex min-h-screen">

        {{-- Filter Sidebar --}}
        <aside class="w-64 shrink-0 border-e border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-6 py-5 flex flex-col gap-6 overflow-y-auto">

            {{-- Sort --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-3">Sort by</p>
                <div class="flex flex-col gap-1">
                    @foreach([
                        'rating'     => 'By rating',
                        'newest'     => 'Newest first',
                        'price_asc'  => 'Price: low to high',
                        'price_desc' => 'Price: high to low',
                    ] as $value => $label)
                        <a href="{{ request()->fullUrlWithQuery(['sort' => $value]) }}"
                           class="text-sm px-3 py-1.5 rounded-lg transition-colors
                                  {{ request('sort', 'rating') === $value
                                     ? 'bg-blue-50 text-blue-700 font-medium dark:bg-blue-950 dark:text-blue-300'
                                     : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Genres --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-3">Genres</p>
                <div class="flex flex-col gap-1">
                    @foreach ($genres as $genre)
                        <a href="{{ request()->fullUrlWithQuery(['genre' => request('genre') == $genre->id ? null : $genre->id]) }}"
                           class="flex items-center justify-between text-sm px-3 py-1.5 rounded-lg transition-colors
                                  {{ request('genre') == $genre->id
                                     ? 'bg-blue-50 text-blue-700 font-medium dark:bg-blue-950 dark:text-blue-300'
                                     : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                            <span>{{ $genre->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Status --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-3">Status</p>
                <div class="flex flex-col gap-1">
                    @foreach(['active' => 'Available', 'draft' => 'Draft', 'archived' => 'Archived'] as $value => $label)
                        <a href="{{ request()->fullUrlWithQuery(['status' => request('status') === $value ? null : $value]) }}"
                           class="text-sm px-3 py-1.5 rounded-lg transition-colors
                                  {{ request('status') === $value
                                     ? 'bg-blue-50 text-blue-700 font-medium dark:bg-blue-950 dark:text-blue-300'
                                     : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Publish Year --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-3">Year</p>
                <div class="flex flex-col gap-1">
                    @foreach(range(date('Y'), date('Y') - 5) as $year)
                        <a href="{{ request()->fullUrlWithQuery(['year' => request('year') == $year ? null : $year]) }}"
                           class="text-sm px-3 py-1.5 rounded-lg transition-colors
                                  {{ request('year') == $year
                                     ? 'bg-blue-50 text-blue-700 font-medium dark:bg-blue-950 dark:text-blue-300'
                                     : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                            {{ $year }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Reset --}}
            @if(request()->hasAny(['genre', 'status', 'year', 'search', 'sort']))
                <a href="{{ route('dashboard') }}"
                   class="text-sm text-center text-red-500 hover:text-red-600 dark:text-red-400 py-1">
                    Clear all filters
                </a>
            @endif

        </aside>

        {{-- Main content --}}
        <div class="flex-1 p-6 overflow-y-auto">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Books</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                        {{ $books->total() }} {{ Str::plural('book', $books->total()) }} found
                    </p>
                </div>
            </div>

            {{-- Books Grid --}}
            @if ($books->isEmpty())
                <div class="flex flex-col items-center justify-center py-24 text-zinc-400">
                    <flux:icon name="book-open" class="w-12 h-12 mb-3" />
                    <p class="text-sm">No books found</p>
                    <a href="{{ route('dashboard') }}" class="mt-3 text-sm text-blue-500 hover:underline">Clear filters</a>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach ($books as $book)
                        <div class="group flex flex-col rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden hover:shadow-md transition-shadow">

                            {{-- Cover --}}
                            <div class="relative aspect-[2/3] bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                @if ($book->cover_image)
                                    <img src="{{ $book->cover_image }}" alt="{{ $book->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <flux:icon name="book-open" class="w-10 h-10 text-zinc-300" />
                                    </div>
                                @endif

                                @if ($book->status === 'active' && $book->stock > 0)
                                    <span class="absolute top-2 left-2 text-xs bg-green-500 text-white px-2 py-0.5 rounded-full font-medium">
                                        In stock
                                    </span>
                                @elseif ($book->stock === 0)
                                    <span class="absolute top-2 left-2 text-xs bg-red-500 text-white px-2 py-0.5 rounded-full font-medium">
                                        Out of stock
                                    </span>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="p-3 flex flex-col gap-1 flex-1">
                                <p class="text-xs text-zinc-400 dark:text-zinc-500 truncate">
                                    {{ $book->author?->name ?? '—' }}
                                </p>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 leading-tight line-clamp-2">
                                    {{ $book->title }}
                                </p>

                                @if ($book->average_rating > 0)
                                    <div class="flex items-center gap-1 mt-auto pt-1">
                                        <flux:icon name="star" class="w-3 h-3 text-yellow-400" />
                                        <span class="text-xs text-zinc-500">
                                            {{ number_format($book->average_rating, 1) }}
                                            ({{ $book->ratings_count }})
                                        </span>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                                    <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                        ${{ number_format($book->price, 2) }}
                                    </span>
                                    <button
                                        class="p-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors"
                                        title="Add to cart">
                                        <flux:icon name="shopping-cart" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($books->hasPages())
                    <div class="mt-8">
                        {{ $books->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</x-layouts::app.header>
