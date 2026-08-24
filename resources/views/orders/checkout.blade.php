<x-layouts::app.header title="Checkout">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                <p class="font-medium">Couldn't place the order:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <flux:button href="{{ route('cart.index') }}" variant="ghost" icon="arrow-left" />
            <div>
                <flux:heading size="xl">Checkout</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Review your order and fill in the details</flux:text>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">

            {{-- Cart items --}}
            <div class="flex-1">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($cartItems as $item)
                            <div class="flex items-center gap-4 p-4">
                                <div class="w-14 h-20 rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 shrink-0">
                                    @if ($item->book?->coverImage)
                                        <img src="{{ Storage::disk('s3')->url($item->book->coverImage) }}" alt="{{ $item->book->title }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <flux:icon name="book-open" class="w-6 h-6 text-zinc-300" />
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-zinc-400 truncate">{{ $item->book?->authorName ?? '—' }}</p>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $item->book?->title }}</p>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mt-1">
                                        ${{ number_format($item->book?->price ?? 0, 2) }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-xs text-zinc-500">x{{ $item->quantity }}</p>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                        ${{ number_format(($item->book?->price ?? 0) * $item->quantity, 2) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Order form + summary --}}
            <div class="w-full lg:w-96 shrink-0">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 sticky top-6">

                    <form method="POST" action="{{ route('checkout.store') }}" class="flex flex-col gap-5"
                          onsubmit="const b=this.querySelector('button[type=submit]'); if (b && !b.disabled) { b.disabled = true; b.textContent = 'Placing order...'; }">
                        @csrf

                        <flux:field>
                            <flux:label>Shipping address</flux:label>
                            <flux:textarea
                                name="shippingAddress"
                                rows="3"
                                placeholder="123 Main St, City"
                                :invalid="$errors->has('shippingAddress')"
                            >{{ old('shippingAddress') }}</flux:textarea>
                            @error('shippingAddress') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>Payment method</flux:label>
                            <flux:select name="paymentMethod" :invalid="$errors->has('paymentMethod')">
                                <flux:select.option value="" disabled :selected="old('paymentMethod', '') === ''">Select payment method...</flux:select.option>
                                <flux:select.option value="card" :selected="old('paymentMethod') === 'card'">Card</flux:select.option>
                                <flux:select.option value="cash" :selected="old('paymentMethod') === 'cash'">Cash</flux:select.option>
                                <flux:select.option value="webpay" :selected="old('paymentMethod') === 'webpay'">WebPay</flux:select.option>
                            </flux:select>
                            @error('paymentMethod') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <div class="border-t border-zinc-100 dark:border-zinc-800 pt-4">
                            <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400">
                                <span>Items ({{ collect($cartItems)->sum('quantity') }})</span>
                                <span>${{ number_format($total, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mt-2">
                                <span>Shipping</span>
                                <span class="text-green-600 font-medium">Free</span>
                            </div>
                            <div class="flex justify-between font-semibold text-zinc-900 dark:text-zinc-100 mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                <span>Total</span>
                                <span>${{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <flux:button type="submit" variant="primary" class="w-full">Place order</flux:button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-layouts::app.header>
