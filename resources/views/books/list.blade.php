<x-layouts::app.header title="Books">
    <div class="flex min-h-screen flex-col">
        <div class="flex-1 flex flex-col gap-6 p-6">

            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">Books</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Manage book catalog</flux:text>
                </div>
                <flux:button href="{{ route('books.create') }}" variant="primary" icon="plus">
                    Add book
                </flux:button>
            </div>

            @if (session('success'))
                <flux:callout variant="success" icon="check-circle">
                    {{ session('success') }}
                </flux:callout>
            @endif

            {{-- Filters --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <form method="GET" action="{{ route('books.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-48 relative" x-data="bookSuggest()" x-ref="wrapper">
                        <flux:input
                            name="search"
                            x-model="query"
                            x-on:input.debounce.300ms="fetchSuggestions"
                            x-on:keydown.escape="close"
                            x-on:focus="query.trim().length >= 2 && fetchSuggestions()"
                            value="{{ request('search') }}"
                            placeholder="Search by name..."
                            icon="magnifying-glass"
                            autocomplete="off"
                        />

                        <template x-teleport="body">
                            <div
                                x-show="open || loading"
                                :style="'position:fixed;z-index:9999;top:'+dropY+'px;left:'+dropX+'px;width:'+dropW+'px'"
                                class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
                            >
                                <div x-show="loading" class="px-4 py-3 text-sm text-zinc-400 text-center">Searching...</div>

                                <ul x-show="!loading && open">
                                    <template x-for="item in results" :key="item.id">
                                        <li>
                                            <a :href="item.url"
                                               class="flex items-center gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold dark:bg-indigo-900 dark:text-indigo-300"
                                                     x-text="item.title.charAt(0).toUpperCase()">
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate" x-text="item.title"></span>
                                                    <span class="text-xs text-zinc-400 truncate" x-text="item.author"></span>
                                                </div>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                    </div>
                    <div class="w-40">
                        <flux:select name="sortBy">
                            <flux:select.option value="id" :selected="request('sortBy') === 'id'">By ID</flux:select.option>
                            <flux:select.option value="title" :selected="request('sortBy') === 'title'">By title</flux:select.option>
                            <flux:select.option value="created_at" :selected="request('sortBy') === 'created_at'">By date</flux:select.option>
                        </flux:select>
                    </div>
                    <div class="w-40">
                        <flux:select name="sortDirection">
                            <flux:select.option value="asc" :selected="request('sortDirection', 'desc') === 'asc'">Ascending</flux:select.option>
                            <flux:select.option value="desc" :selected="request('sortDirection', 'desc') === 'desc'">Descending</flux:select.option>
                        </flux:select>
                    </div>
                    <flux:button type="submit" variant="filled">Apply</flux:button>
                    @if(request('search') || request('sortBy'))
                        <flux:button href="{{ route('books.index') }}" variant="ghost">Reset</flux:button>
                    @endif
                </form>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-700 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider w-12">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Cover</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($books->items as $book)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <td class="px-6 py-4 text-sm text-zinc-400">{{ $book->id }}</td>
                            <td class="px-6 py-4">
                                <div class="w-10 h-14 rounded overflow-hidden bg-zinc-100 dark:bg-zinc-700">
                                    @if ($book->cover_image)
                                        <img src="{{ Storage::url($book->cover_image) }}" alt="{{ $book->title }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <flux:icon name="book-open" class="w-4 h-4 text-zinc-300" />
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 max-w-xs truncate">{{ $book->title }}</p>
                                <p class="text-xs text-zinc-400 mt-0.5">{{ $book->publish_year }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400">{{ $book->author?->name }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">${{ number_format($book->price, 2) }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClass = match($book->status) {
                                        'active' => 'bg-green-100 text-green-700',
                                        'draft'     => 'bg-yellow-100 text-yellow-700',
                                        default     => 'bg-zinc-100 text-zinc-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
        {{ ucfirst($book->status) }}
    </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <flux:button href="{{ route('books.show', $book) }}" variant="ghost" size="sm" icon="eye">View</flux:button>
                                    <flux:button href="{{ route('books.edit', $book) }}" variant="ghost" size="sm" icon="pencil">Edit</flux:button>
                                    <div>
                                        <flux:modal.trigger name="delete-book-{{ $book->id }}">
                                            <flux:button variant="ghost" size="sm" icon="trash">Delete</flux:button>
                                        </flux:modal.trigger>

                                        <flux:modal name="delete-book-{{ $book->id }}" class="min-w-[22rem] text-left">
                                            <flux:heading size="lg">Delete book?</flux:heading>
                                            <flux:subheading>Are you sure you want to delete "{{ $book->title }}"?</flux:subheading>

                                            <div class="flex gap-2 mt-6 justify-end">
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">Cancel</flux:button>
                                                </flux:modal.close>

                                                <form action="{{ route('books.destroy', $book) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <flux:button type="submit" variant="danger">Delete</flux:button>
                                                </form>
                                            </div>
                                        </flux:modal>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-sm text-zinc-400">
                                No books yet
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                @if ($books->lastPage > 1)
                    <div class="border-t border-zinc-200 dark:border-zinc-700 px-6 py-4">
                        {!! $books->links !!}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        function bookSuggest() {
            return {
                query: '{{ addslashes(request('search', '')) }}',
                results: [],
                open: false,
                loading: false,
                dropX: 0, dropY: 0, dropW: 300,
                _abort: null,

                init() {
                    document.addEventListener('click', (e) => {
                        if (!this.$refs.wrapper.contains(e.target)) this.close();
                    });
                    window.addEventListener('scroll', () => this.close(), { passive: true });
                    window.addEventListener('resize', () => this.close(), { passive: true });
                },

                updatePosition() {
                    const rect = this.$refs.wrapper.getBoundingClientRect();
                    this.dropX = rect.left;
                    this.dropY = rect.bottom + 6;
                    this.dropW = rect.width;
                },

                async fetchSuggestions() {
                    const q = this.query.trim();
                    if (q.length < 2) { this.close(); return; }

                    this.updatePosition();
                    if (this._abort) this._abort.abort();
                    this._abort = new AbortController();
                    this.open = false;
                    this.loading = true;

                    try {
                        const res = await fetch('{{ route('api.books.suggest') }}?q=' + encodeURIComponent(q), {
                            signal: this._abort.signal,
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        if (!res.ok) { this.close(); return; }
                        const data = await res.json();
                        this.results = Array.isArray(data) ? data : [];
                        this.open = this.results.length > 0;
                    } catch (e) {
                        if (e.name !== 'AbortError') this.close();
                    } finally {
                        this.loading = false;
                    }
                },

                close() { this.open = false; this.loading = false; },
            };
        }
    </script>

</x-layouts::app.header>
