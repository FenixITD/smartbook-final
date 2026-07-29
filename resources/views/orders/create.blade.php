<x-layouts::app.header title="Create Order">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <div class="flex items-center gap-4">
            <flux:button href="{{ route('orders.index') }}" variant="ghost" icon="arrow-left" />
            <div>
                <flux:heading size="xl">New order</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Select a user, add books, and place the order</flux:text>
            </div>
        </div>

        <div class="max-w-xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                        <p class="font-medium">Couldn't create the order:</p>
                        <ul class="mt-1 list-disc pl-5">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('orders.store') }}" class="flex flex-col gap-5">
                    @csrf

                    <flux:field>
                        <flux:label for="userId">User ID</flux:label>
                        <flux:input id="userId" name="userId" type="number" min="1" value="{{ old('userId') }}"
                                   :invalid="$errors->has('userId')" />
                        @error('userId') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    {{-- Order items --}}
                    <div x-data="orderItems()">
                        <div class="flex items-center justify-between mb-2">
                            <flux:label>Order items</flux:label>
                        </div>

                        <div class="flex gap-2 items-end">
                            <div class="flex-1 relative" x-ref="bookSearch">
                                <flux:input
                                    x-model="query"
                                    x-on:input.debounce.300ms="fetchSuggestions"
                                    x-on:keydown.escape="close"
                                    x-on:keydown.enter.prevent="addItem"
                                    x-on:focus="query.trim().length >= 2 && fetchSuggestions()"
                                    placeholder="Search book by title..."
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
                                            <template x-for="book in results" :key="book.id">
                                                <li>
                                                    <button type="button"
                                                            x-on:click="selectBook(book)"
                                                            class="w-full text-left flex items-center gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold dark:bg-indigo-900 dark:text-indigo-300"
                                                             x-text="book.title.charAt(0).toUpperCase()">
                                                        </div>
                                                        <div class="flex flex-col min-w-0">
                                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate" x-text="book.title"></span>
                                                            <span class="text-xs text-zinc-400 truncate" x-text="book.author"></span>
                                                        </div>
                                                    </button>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>

                            <div class="w-24">
                                <flux:input type="number" x-model="newQuantity" min="1" placeholder="Qty" />
                            </div>

                            <flux:button type="button" x-on:click="addItem" variant="primary" icon="plus">Add</flux:button>
                        </div>

                        <template x-if="items.length > 0">
                            <div class="mt-4 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase">Book</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase w-20">Qty</th>
                                            <th class="px-4 py-2 text-right w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-zinc-900 dark:text-zinc-100" x-text="item.title"></td>
                                                <td class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400" x-text="item.quantity"></td>
                                                <td class="px-4 py-2 text-right">
                                                    <button type="button" x-on:click="removeItem(index)" class="text-red-500 hover:text-red-700 text-sm font-medium">Remove</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template x-for="(item, index) in items" :key="'hidden-' + index">
                            <div>
                                <input type="hidden" :name="'items[' + index + '][bookId]'" x-bind:value="item.bookId">
                                <input type="hidden" :name="'items[' + index + '][quantity]'" x-bind:value="item.quantity">
                            </div>
                        </template>
                    </div>

                    <flux:field>
                        <flux:label for="shippingAddress">Shipping address</flux:label>
                        <flux:input id="shippingAddress" name="shippingAddress" value="{{ old('shippingAddress') }}"
                                   placeholder="123 Main St, City" :invalid="$errors->has('shippingAddress')" />
                        @error('shippingAddress') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="paymentMethod">Payment method</flux:label>
                        <flux:select id="paymentMethod" name="paymentMethod" required :invalid="$errors->has('paymentMethod')">
                            <flux:select.option value="" disabled :selected="old('paymentMethod', '') === ''">Select payment method...</flux:select.option>
                            <flux:select.option value="card" :selected="old('paymentMethod') === 'card'">Card</flux:select.option>
                            <flux:select.option value="cash" :selected="old('paymentMethod') === 'cash'">Cash</flux:select.option>
                            <flux:select.option value="webpay" :selected="old('paymentMethod') === 'webpay'">WebPay</flux:select.option>
                        </flux:select>
                        @error('paymentMethod') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <div class="flex gap-3 pt-2">
                        <flux:button type="submit" variant="primary">Create order</flux:button>
                        <flux:button href="{{ route('orders.index') }}" variant="ghost">Cancel</flux:button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function orderItems() {
            return {
                query: '',
                results: [],
                open: false,
                loading: false,
                items: [],
                newQuantity: 1,
                dropX: 0, dropY: 0, dropW: 300,
                _abort: null,

                init() {
                    document.addEventListener('click', (e) => {
                        if (!this.$refs.bookSearch.contains(e.target)) this.close();
                    });
                    window.addEventListener('scroll', () => this.close(), { passive: true });
                    window.addEventListener('resize', () => this.close(), { passive: true });
                },

                updatePosition() {
                    const rect = this.$refs.bookSearch.getBoundingClientRect();
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

                selectBook(book) {
                    const existing = this.items.find(i => i.bookId === book.id);
                    if (existing) {
                        existing.quantity += this.newQuantity;
                    } else {
                        this.items.push({
                            bookId: book.id,
                            title: book.title,
                            quantity: this.newQuantity,
                        });
                    }
                    this.query = '';
                    this.results = [];
                    this.open = false;
                    this.newQuantity = 1;
                },

                addItem() {
                    if (this.results.length === 1) {
                        this.selectBook(this.results[0]);
                    }
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                close() { this.open = false; this.loading = false; },
            };
        }
    </script>
</x-layouts::app.header>
