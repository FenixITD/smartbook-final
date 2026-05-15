<x-layouts::app.header title="Recent Activities">
    <div class="flex min-h-screen flex-col">
        <div class="flex-1 flex flex-col gap-6 p-6 max-w-3xl mx-auto w-full">

            <div>
                <flux:heading size="xl">Recent Activities</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Your latest actions with books</flux:text>
            </div>

            @if ($logs->items === [])
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 px-6 py-16 text-center">
                    <div class="flex justify-center mb-3">
                        <flux:icon name="clock" class="w-10 h-10 text-zinc-300" />
                    </div>
                    <flux:text class="text-zinc-400">No activity yet. Start exploring books!</flux:text>
                    <div class="mt-4">
                        <flux:button href="{{ route('dashboard') }}" variant="primary" size="sm">Browse books</flux:button>
                    </div>
                </div>
            @else
                <div class="flex flex-col gap-3">
                    @foreach ($logs->items as $log)
                        @php
                            $isCart     = $log->logName === 'CartItem';
                            $isFavorite = $log->logName === 'Favorite';
                            $isAdded    = in_array($log->description, ['added', 'created']);
                            $isRemoved  = in_array($log->description, ['deleted']);

                            // book_id может быть в разных местах в зависимости от типа лога:
                            // - CartItem (ручной лог):     properties['book_id']
                            // - Favorite created (трейт):  properties['attributes']['book_id']
                            // - Favorite deleted (трейт):  properties['old']['book_id']
                            $bookId = $log->properties['book_id']
                                ?? $log->properties['attributes']['book_id']
                                ?? $log->properties['old']['book_id']
                                ?? null;

                            $bookTitle = $bookId !== null
                                ? ($booksById[$bookId]->title ?? null)
                                : null;

                            $quantity = $log->properties['quantity'] ?? null;

                            if ($isCart && $isAdded) {
                                $icon        = 'shopping-cart';
                                $iconColor   = 'text-blue-500';
                                $bgColor     = 'bg-blue-50 dark:bg-blue-950';
                                $borderColor = 'border-blue-100 dark:border-blue-900';
                                $actionText  = 'Added to cart';
                            } elseif ($isCart && $isRemoved) {
                                $icon        = 'shopping-cart';
                                $iconColor   = 'text-red-400';
                                $bgColor     = 'bg-red-50 dark:bg-red-950';
                                $borderColor = 'border-red-100 dark:border-red-900';
                                $actionText  = 'Removed from cart';
                            } elseif ($isFavorite && $isAdded) {
                                $icon        = 'heart';
                                $iconColor   = 'text-pink-500';
                                $bgColor     = 'bg-pink-50 dark:bg-pink-950';
                                $borderColor = 'border-pink-100 dark:border-pink-900';
                                $actionText  = 'Added to favorites';
                            } elseif ($isFavorite && $isRemoved) {
                                $icon        = 'heart';
                                $iconColor   = 'text-red-400';
                                $bgColor     = 'bg-red-50 dark:bg-red-950';
                                $borderColor = 'border-red-100 dark:border-red-900';
                                $actionText  = 'Removed from favorites';
                            } else {
                                $icon        = 'clock';
                                $iconColor   = 'text-zinc-400';
                                $bgColor     = 'bg-zinc-50 dark:bg-zinc-800';
                                $borderColor = 'border-zinc-100 dark:border-zinc-700';
                                $actionText  = ucfirst($log->description);
                            }
                        @endphp

                        <div class="flex items-center gap-4 rounded-xl border {{ $borderColor }} {{ $bgColor }} px-5 py-4">

                            <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-zinc-900 shadow-sm">
                                <flux:icon name="{{ $icon }}" class="w-5 h-5 {{ $iconColor }}" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                    {{ $actionText }}
                                    @if ($bookId)
                                        <span class="text-zinc-500 font-normal">
                                            — Book #{{ $bookId }}
                                            @if ($bookTitle)
                                                «{{ $bookTitle }}»
                                            @endif
                                        </span>
                                    @endif
                                </p>
                                @if ($quantity !== null)
                                    <p class="text-xs text-zinc-400 mt-0.5">Quantity: {{ $quantity }}</p>
                                @endif
                            </div>

                            <p class="flex-shrink-0 text-xs text-zinc-400 whitespace-nowrap">
                                {{ $log->createdAt }}
                            </p>

                        </div>
                    @endforeach
                </div>

                @if ($logs->lastPage > 1)
                    <div class="pt-2">
                        {!! $logs->links !!}
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-layouts::app.header>
