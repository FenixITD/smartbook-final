<x-layouts::app.header title="Orders">
    <div class="flex min-h-screen flex-col">

        <div class="flex-1 flex flex-col gap-6 p-6">

            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">Orders</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Manage customer orders</flux:text>
                </div>
                <flux:button href="{{ route('orders.create') }}" variant="primary" icon="plus">
                    Add order
                </flux:button>
            </div>

            @if (session('success'))
                <flux:callout variant="success" icon="check-circle">
                    {{ session('success') }}
                </flux:callout>
            @endif

            {{-- Filters --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-48 relative" x-data="orderSuggest()" x-ref="wrapper">
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
                                                <span class="text-xs font-bold text-zinc-400" x-text="'#' + item.id"></span>
                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100" x-text="item.user_name"></span>
                                                <span class="text-xs text-zinc-400" x-text="item.status"></span>
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
                            <flux:select.option value="user_name" :selected="request('sortBy') === 'user_name'">By name</flux:select.option>
                            <flux:select.option value="status" :selected="request('sortBy') === 'status'">By status</flux:select.option>
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
                        <flux:button href="{{ route('orders.index') }}" variant="ghost">Reset</flux:button>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Shipping address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Created at</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($paginated->items as $order)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                <td class="px-6 py-4 text-sm text-zinc-400 dark:text-zinc-500">{{ $order->id }}</td>
                                <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $order->user?->name ?? 'User #'.$order->user_id }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    ${{ number_format($order->total, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'pending'   => 'bg-yellow-100 text-yellow-700',
                                            'paid'      => 'bg-blue-100 text-blue-700',
                                            'shipped'   => 'bg-purple-100 text-purple-700',
                                            'delivered' => 'bg-green-100 text-green-700',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400 max-w-xs truncate">
                                    {{ $order->shipping_address }}
                                </td>
                                <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $order->created_at->format('d.m.Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <flux:button href="{{ route('orders.show', $order) }}" variant="ghost" size="sm" icon="eye">View</flux:button>
                                        <flux:button href="{{ route('orders.edit', $order) }}" variant="ghost" size="sm" icon="pencil">Edit</flux:button>
                                        <div>
                                            <flux:modal.trigger name="delete-order-{{ $order->id }}">
                                                <flux:button variant="ghost" size="sm" icon="trash">Delete</flux:button>
                                            </flux:modal.trigger>

                                            <flux:modal name="delete-order-{{ $order->id }}" class="min-w-[22rem] text-left">
                                                <flux:heading size="lg">Delete order?</flux:heading>
                                                <flux:subheading>Are you sure you want to delete order #{{ $order->id }}?</flux:subheading>

                                                <div class="flex gap-2 mt-6 justify-end">
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost">Cancel</flux:button>
                                                    </flux:modal.close>

                                                    <form action="{{ route('orders.destroy', $order) }}" method="POST">
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
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-zinc-400">
                                        <flux:icon name="shopping-bag" class="w-10 h-10" />
                                        <span class="text-sm">No orders yet</span>
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
                Total orders: {{ $paginated->total }}
            </flux:text>
        </div>
    </div>

    <script>
        function orderSuggest() {
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
                        const res = await fetch('{{ route('api.orders.suggest') }}?q=' + encodeURIComponent(q), {
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
