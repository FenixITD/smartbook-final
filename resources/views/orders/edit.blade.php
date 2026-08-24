<x-layouts::app.header title="Edit Order">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <div class="flex items-center gap-4">
            <flux:button href="{{ route('orders.index') }}" variant="ghost" icon="arrow-left" />
            <div>
                <flux:heading size="xl">Edit order</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Order #{{ $order->id }}</flux:text>
            </div>
        </div>

        <div class="max-w-xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <form method="POST" action="{{ route('orders.update', $order->id) }}" class="flex flex-col gap-5">
                    @csrf
                    @method('PUT')

                    <flux:field>
                        <flux:label for="userId">User ID</flux:label>
                        <flux:input id="userId" name="userId" type="number" min="1" value="{{ old('userId', $order->userId) }}" :invalid="$errors->has('userId')" />
                        @error('userId') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="total">Total</flux:label>
                        <flux:text class="text-sm font-bold">${{ number_format($order->total, 2) }}</flux:text>
                    </flux:field>

                    <flux:field>
                        <flux:label for="status">Status</flux:label>
                        <flux:select id="status" name="status">
                            <flux:select.option value="pending"   :selected="old('status', $order->status) === 'pending'">Pending</flux:select.option>
                            <flux:select.option value="paid"      :selected="old('status', $order->status) === 'paid'">Paid</flux:select.option>
                            <flux:select.option value="shipped"   :selected="old('status', $order->status) === 'shipped'">Shipped</flux:select.option>
                            <flux:select.option value="delivered" :selected="old('status', $order->status) === 'delivered'">Delivered</flux:select.option>
                            <flux:select.option value="cancelled" :selected="old('status', $order->status) === 'cancelled'">Cancelled</flux:select.option>
                        </flux:select>
                        @error('status') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="shippingAddress">Shipping address</flux:label>
                        <flux:input id="shippingAddress" name="shippingAddress" value="{{ old('shippingAddress', $order->shippingAddress) }}" :invalid="$errors->has('shippingAddress')" />
                        @error('shippingAddress') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:select name="paymentMethod" label="Payment method">
                        <flux:select.option value="" disabled selected>Select payment method...</flux:select.option>
                        <flux:select.option value="card" :selected="old('paymentMethod', $order->paymentMethod ?? '') === 'card'">Card</flux:select.option>
                        <flux:select.option value="cash" :selected="old('paymentMethod', $order->paymentMethod ?? '') === 'cash'">Cash</flux:select.option>
                        <flux:select.option value="webpay" :selected="old('paymentMethod', $order->paymentMethod ?? '') === 'webpay'">WebPay</flux:select.option>
                    </flux:select>

                    <div class="flex gap-3 pt-2">
                        <flux:button type="submit" variant="primary">Save changes</flux:button>
                        <flux:button href="{{ route('orders.show', $order->id) }}" variant="ghost">Cancel</flux:button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Danger zone --}}
        <div class="max-w-xl">
            <div class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-900 dark:bg-red-950">
                <flux:heading size="sm" class="text-red-700 dark:text-red-400">Danger zone</flux:heading>
                <flux:text class="mt-1 text-red-600 dark:text-red-400 text-sm">
                    Deleting an order is irreversible.
                </flux:text>
                <div class="mt-4">
                    <div>
                        <flux:modal.trigger name="delete-order-{{ $order->id }}">
                            <flux:button variant="danger" icon="trash">Delete order</flux:button>
                        </flux:modal.trigger>

                        <flux:modal name="delete-order-{{ $order->id }}" class="min-w-[22rem]">
                            <flux:heading size="lg">Delete order?</flux:heading>
                            <flux:subheading>Are you sure you want to delete order #{{ $order->id }}? This action cannot be undone.</flux:subheading>

                            <div class="flex gap-2 mt-6 justify-end">
                                <flux:modal.close>
                                    <flux:button variant="ghost">Cancel</flux:button>
                                </flux:modal.close>

                                <form action="{{ route('orders.destroy', $order->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button type="submit" variant="danger">Delete order</flux:button>
                                </form>
                            </div>
                        </flux:modal>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts::app.header>
