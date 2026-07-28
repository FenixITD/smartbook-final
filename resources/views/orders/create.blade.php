<x-layouts::app.header title="Create Order">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <div class="flex items-center gap-4">
            <flux:button href="{{ route('orders.index') }}" variant="ghost" icon="arrow-left" />
            <div>
                <flux:heading size="xl">New order</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Fill in the order details</flux:text>
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
                        <flux:input id="userId" name="userId" type="number" min="1" value="{{ old('userId') }}" :invalid="$errors->has('userId')" />
                        @error('userId') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="total">Total ($)</flux:label>
                        <flux:input id="total" name="total" type="number" step="0.01" min="0" value="{{ old('total') }}" :invalid="$errors->has('total')" />
                        @error('total') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="status">Status</flux:label>
                        <flux:select id="status" name="status" :invalid="$errors->has('status')">
                            <flux:select.option value="pending"   :selected="old('status') === 'pending'">Pending</flux:select.option>
                            <flux:select.option value="paid"      :selected="old('status') === 'paid'">Paid</flux:select.option>
                            <flux:select.option value="shipped"   :selected="old('status') === 'shipped'">Shipped</flux:select.option>
                            <flux:select.option value="delivered" :selected="old('status') === 'delivered'">Delivered</flux:select.option>
                            <flux:select.option value="cancelled" :selected="old('status') === 'cancelled'">Cancelled</flux:select.option>
                        </flux:select>
                        @error('status') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="shippingAddress">Shipping address</flux:label>
                        <flux:input id="shippingAddress" name="shippingAddress" value="{{ old('shippingAddress') }}" placeholder="123 Main St, City" :invalid="$errors->has('shippingAddress')" />
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
</x-layouts::app.header>
