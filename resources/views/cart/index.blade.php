<x-layouts::app.header title="Cart">
    <div class="flex min-h-screen flex-col">
        <div class="flex-1 flex flex-col lg:flex-row gap-6 p-6">

            <div class="flex-1 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Cart</h2>
                        <p class="text-sm text-zinc-500 mt-0.5">{{ count($cartItems) }} {{ Str::plural('item', count($cartItems)) }}</p>
                    </div>
                    @if (count($cartItems) > 0)
                        <div>

                            <flux:modal.trigger name="clear-cart">
                                <button type="button"
                                        class="flex items-center gap-1.5 text-sm text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950 px-3 py-1.5 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Remove all
                                </button>
                            </flux:modal.trigger>

                            <flux:modal name="clear-cart" class="min-w-[22rem]">
                                <flux:heading size="lg">Clear cart?</flux:heading>
                                <flux:subheading>Are you sure you want to remove all items from your cart? This action cannot be undone.</flux:subheading>

                                <div class="flex gap-2 mt-6 justify-end">
                                    <flux:modal.close>
                                        <flux:button variant="ghost">Cancel</flux:button>
                                    </flux:modal.close>

                                    <form action="{{ route('cart.clear') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" variant="danger">Remove all</flux:button>
                                    </form>
                                </div>
                            </flux:modal>

                        </div>
                    @endif
                </div>

                @if (session('success'))
                    <flux:callout variant="success" icon="check-circle">
                        {{ session('success') }}
                    </flux:callout>
                @endif

                {{-- Show stock validation errors globally at the top of the cart --}}
                @error('quantity')
                <flux:callout variant="danger" icon="exclamation-triangle">
                    {{ $message }}
                </flux:callout>
                @enderror

                @if (count($cartItems) === 0)
                    <div class="flex flex-col items-center justify-center py-24 text-zinc-400 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                        <flux:icon name="shopping-cart" class="w-12 h-12 mb-3" />
                        <p class="text-sm">Your cart is empty</p>
                        <a href="{{ route('dashboard') }}" class="mt-3 text-sm text-blue-500 hover:underline">Browse books</a>
                    </div>
                @else
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($cartItems as $item)
                                <div class="flex items-center gap-4 p-4">

                                    {{-- Cover --}}
                                    <div class="w-14 h-20 rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 shrink-0">
                                        @if ($item->book->coverImage)
                                            <img src="{{ Storage::url($item->book->coverImage) }}" alt="{{ $item->book->title }}"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <flux:icon name="book-open" class="w-6 h-6 text-zinc-300" />
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-zinc-400 truncate">{{ $item->book->authorName ?? '—' }}</p>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $item->book->title }}</p>
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mt-1">
                                            ${{ number_format($item->book->price, 2) }}
                                        </p>
                                    </div>

                                    {{-- Quantity --}}
                                    <div class="flex items-center gap-2">
                                        @if ($item->quantity > 1)
                                            {{-- Reduce quantity --}}
                                            <form action="{{ route('cart.update', $item->bookId) }}" method="POST" class="m-0 flex">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" name="quantity" value="{{ $item->quantity - 1 }}"
                                                        class="w-7 h-7 rounded-lg border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors text-sm font-medium">
                                                    −
                                                </button>
                                            </form>
                                        @else
                                            {{-- Delete this item as quantity is 1 --}}
                                            <form action="{{ route('cart.destroy', $item->bookId) }}" method="POST" class="m-0 flex">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="w-7 h-7 rounded-lg border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-600 dark:text-zinc-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950 dark:hover:text-red-500 transition-colors text-sm font-medium">
                                                    −
                                                </button>
                                            </form>
                                        @endif

                                        <span class="w-8 text-center text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $item->quantity }}
                                        </span>

                                        {{-- Increase quantity --}}
                                        <form action="{{ route('cart.update', $item->bookId) }}" method="POST" class="m-0 flex">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" name="quantity" value="{{ min(99, $item->quantity + 1) }}"
                                                    class="w-7 h-7 rounded-lg border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors text-sm font-medium">
                                                +
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Subtotal --}}
                                    <div class="w-20 text-right shrink-0">
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                            ${{ number_format($item->book->price * $item->quantity, 2) }}
                                        </p>
                                    </div>

                                    {{-- Remove --}}
                                    <div class="flex items-center">

                                        <flux:modal.trigger name="delete-cart-item-{{ $item->bookId }}">
                                            <button type="button"
                                                    class="p-1.5 text-zinc-400 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50 dark:hover:bg-red-950">
                                                <flux:icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </flux:modal.trigger>

                                        <flux:modal name="delete-cart-item-{{ $item->bookId }}" class="min-w-[22rem]">
                                            <flux:heading size="lg">Remove item?</flux:heading>
                                            <flux:subheading>Are you sure you want to remove "{{ $item->book->title }}" from your cart?</flux:subheading>

                                            <div class="flex gap-2 mt-6 justify-end">
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">Cancel</flux:button>
                                                </flux:modal.close>

                                                <form action="{{ route('cart.destroy', $item->bookId) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <flux:button type="submit" variant="danger">Remove</flux:button>
                                                </form>
                                            </div>
                                        </flux:modal>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Order Summary --}}
            @if (count($cartItems) > 0)
                <div class="w-full lg:w-80 shrink-0">
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 sticky top-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Order summary</h3>

                        <div class="flex flex-col gap-3 text-sm">
                            <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                <span>Items ({{ collect($cartItems)->sum('quantity') }})</span>
                                <span>${{ number_format($total, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                <span>Shipping</span>
                                <span class="text-green-600 font-medium">Free</span>
                            </div>
                            <div class="border-t border-zinc-100 dark:border-zinc-800 pt-3 flex justify-between font-semibold text-zinc-900 dark:text-zinc-100">
                                <span>Total</span>
                                <span>${{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        @auth
                            <a href="{{ route('checkout.create') }}" class="block w-full mt-5">
                                <flux:button variant="primary" class="w-full">Checkout</flux:button>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="block w-full mt-5 text-center py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors">
                                Sign in to checkout
                            </a>
                        @endauth

                        <a href="{{ route('dashboard') }}"
                           class="block text-center text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 mt-3">
                            Continue shopping
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-layouts::app.header>
