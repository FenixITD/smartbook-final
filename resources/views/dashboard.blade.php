<x-layouts::app.header title="Dashboard">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-5xl mx-auto py-6">

            {{-- Banner + Genres row --}}
            @if(!request()->hasAny(['genre', 'status', 'year', 'search', 'sort']))
                <div class="flex gap-4 mb-8">

                    {{-- Genres list --}}
                    <div class="w-52 shrink-0 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-3">Genres</p>
                        <div class="flex flex-col gap-0.5">
                            @foreach ($genres as $genre)
                                <a href="{{ request()->fullUrlWithQuery(['genre' => $genre->id]) }}"
                                   class="text-sm px-2 py-1.5 rounded-lg transition-colors text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100">
                                    {{ $genre->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Banner --}}
                    <div class="flex-1 rounded-xl overflow-hidden relative min-h-52"
                         style="background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 50%, #1a252f 100%);">
                        {{-- Decorative circles --}}
                        <div class="absolute top-0 right-0 w-72 h-72 rounded-full opacity-10"
                             style="background: radial-gradient(circle, #60a5fa, transparent); transform: translate(30%, -30%);"></div>
                        <div class="absolute bottom-0 left-0 w-56 h-56 rounded-full opacity-10"
                             style="background: radial-gradient(circle, #a78bfa, transparent); transform: translate(-30%, 30%);"></div>

                        <div class="relative h-full flex flex-col justify-center px-10 py-8">
                            <p class="text-blue-300 text-sm font-medium uppercase tracking-widest mb-2">New arrivals</p>
                            <h2 class="text-white text-3xl font-bold leading-tight mb-3">
                                Discover Your<br>Next Favourite Book
                            </h2>
                            <p class="text-blue-200 text-sm mb-6 max-w-sm">
                                Browse our curated collection of books across all genres. Free shipping on orders over $50.
                            </p>
                            <div class="flex gap-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}"
                                   class="inline-flex items-center gap-2 bg-white text-zinc-900 text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-zinc-100 transition-colors">
                                    <flux:icon name="sparkles" class="w-4 h-4" />
                                    New arrivals
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}"
                                   class="inline-flex items-center gap-2 border border-white/30 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-white/10 transition-colors">
                                    Best prices
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Filters bar --}}
            <div class="flex flex-wrap items-center gap-3 mb-6">
                {{-- Sort --}}
                <div class="flex items-center gap-1 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-1 py-1">
                    @foreach(['rating' => 'Top rated', 'newest' => 'Newest', 'price_asc' => 'Cheapest', 'price_desc' => 'Priciest'] as $value => $label)
                        <a href="{{ request()->fullUrlWithQuery(['sort' => $value]) }}"
                           class="text-sm px-3 py-1.5 rounded-md transition-colors
                                  {{ request('sort', 'rating') === $value
                                     ? 'bg-blue-600 text-white font-medium'
                                     : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                {{-- Active genre filter --}}
                @if(request('genre'))
                    @php $activeGenre = collect($genres)->firstWhere('id', (int) request('genre')); @endphp
                    @if($activeGenre)
                        <a href="{{ request()->fullUrlWithQuery(['genre' => null]) }}"
                           class="inline-flex items-center gap-1.5 text-sm bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 px-3 py-1.5 rounded-lg font-medium">
                            {{ $activeGenre->name }}
                            <flux:icon name="x-mark" class="w-3 h-3" />
                        </a>
                    @endif
                @endif

                {{-- Active status filter --}}
                @if(request('status'))
                    <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}"
                       class="inline-flex items-center gap-1.5 text-sm bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 px-3 py-1.5 rounded-lg font-medium">
                        {{ ucfirst(request('status')) }}
                        <flux:icon name="x-mark" class="w-3 h-3" />
                    </a>
                @endif

                {{-- Active year filter --}}
                @if(request('year'))
                    <a href="{{ request()->fullUrlWithQuery(['year' => null]) }}"
                       class="inline-flex items-center gap-1.5 text-sm bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 px-3 py-1.5 rounded-lg font-medium">
                        {{ request('year') }}
                        <flux:icon name="x-mark" class="w-3 h-3" />
                    </a>
                @endif

                @if(request()->hasAny(['genre', 'status', 'year']))
                    <a href="{{ route('dashboard') }}" class="text-sm text-red-500 hover:text-red-600 ml-auto">
                        Clear all
                    </a>
                @endif

                <span class="text-sm text-zinc-400 ml-auto">
                    {{ $paginated->total }} {{ Str::plural('book', $paginated->total) }}
                </span>
            </div>

            {{-- Books Grid --}}
            @if (empty($paginated->items))
                <div class="flex flex-col items-center justify-center py-24 text-zinc-400">
                    <flux:icon name="book-open" class="w-12 h-12 mb-3" />
                    <p class="text-sm">No books found</p>
                    <a href="{{ route('dashboard') }}" class="mt-3 text-sm text-blue-500 hover:underline">Clear filters</a>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    @foreach ($paginated->items as $book)
                        <div class="group flex flex-col rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5">

                            {{-- Кликабельная часть: обложка + инфо --}}
                            <a href="{{ route('catalog.show', $book->id) }}" class="flex flex-col flex-1">

                                {{-- Cover --}}
                                <div class="relative aspect-[2/3] bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                    @if ($book->cover_image)
                                        <img src="{{ Storage::url($book->cover_image) }}"
                                             alt="{{ $book->title }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-zinc-200 dark:bg-zinc-700">
                                            <flux:icon name="book-open" class="w-10 h-10 text-zinc-400" />
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
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 truncate">{{ $book->author?->name ?? '—' }}</p>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 leading-tight line-clamp-2">{{ $book->title }}</p>

                                    @if ($book->average_rating > 0)
                                        <div class="flex items-center gap-1 mt-auto pt-1">
                                            <flux:icon name="star" class="w-3 h-3 text-yellow-400" />
                                            <span class="text-xs text-zinc-500">{{ number_format($book->average_rating, 1) }} ({{ $book->ratings_count }})</span>
                                        </div>
                                    @endif

                                    <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                                        ${{ number_format($book->price, 2) }}
                                    </p>
                                </div>

                            </a>

                            {{-- Кнопка корзины — отдельно от ссылки --}}
                            <div class="px-3 pb-3">
                                <form action="{{ route('cart.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit"
                                            class="w-full py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium transition-colors flex items-center justify-center gap-1.5"
                                        {{ $book->stock === 0 ? 'disabled' : '' }}>
                                        <flux:icon name="shopping-cart" class="w-3.5 h-3.5" />
                                        Add to cart
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($paginated->lastPage > 1)
                    @php
                        $query = request()->query();
                        $current = $paginated->currentPage;
                        $last = $paginated->lastPage;
                    @endphp
                    <div class="mt-8 flex items-center justify-between">
                        <p class="text-sm text-zinc-500">
                            Showing {{ $paginated->perPage * ($current - 1) + 1 }}–{{ min($paginated->perPage * $current, $paginated->total) }}
                            of {{ $paginated->total }}
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
