<x-layouts::app.header title="Order">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <flux:button href="{{ route('orders.index') }}" variant="ghost" icon="arrow-left" />
                <div>
                    <flux:heading size="xl">Order #{{ $order->id }}</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Order details</flux:text>
                </div>
            </div>
            <flux:button href="{{ route('orders.edit', $order->id) }}" variant="primary" icon="pencil">Edit</flux:button>
        </div>

        <div class="max-w-xl">
            <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-700 dark:bg-zinc-900">

                {{-- Header --}}
                <div class="bg-gradient-to-br from-zinc-50 to-zinc-100 dark:from-zinc-900 dark:to-zinc-800 px-6 py-6 flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-zinc-700 text-white text-xl font-bold shadow-md">
                        #{{ $order->id }}
                    </div>
                    <div>
                        <div class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $order->user?->name ?? 'User #'.$order->userId }}
                        </div>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'paid' => 'bg-blue-100 text-blue-700',
                                'shipped' => 'bg-purple-100 text-purple-700',
                                'delivered' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-1 {{ $statusColors[$order->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <div class="px-6 py-4 flex justify-between items-center">
                        <flux:text class="text-zinc-500 text-sm">Total</flux:text>
                        <flux:text class="text-sm font-bold">${{ number_format($order->total, 2) }}</flux:text>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <flux:text class="text-zinc-500 text-sm">Shipping address</flux:text>
                        <flux:text class="text-sm font-medium">{{ $order->shippingAddress }}</flux:text>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <flux:text class="text-zinc-500 text-sm">Payment method</flux:text>
                        <flux:text class="text-sm font-medium">{{ $order->paymentMethod ?? '—' }}</flux:text>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <flux:text class="text-zinc-500 text-sm">Created at</flux:text>
                        <flux:text class="text-sm font-medium">{{ $order->createdAt }}</flux:text>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <flux:text class="text-zinc-500 text-sm">Updated at</flux:text>
                        <flux:text class="text-sm font-medium">{{ $order->updatedAt }}</flux:text>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts::app.header>
