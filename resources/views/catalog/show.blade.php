<x-layouts::app.header title="{{ $book->title }}">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-5xl mx-auto py-8 px-6">

            <flux:button href="{{ route('dashboard') }}" variant="ghost" icon="arrow-left" class="mb-6">
                Back to catalog
            </flux:button>

            <style>
                .book-cover-placeholder {
                    width: 320px;
                    height: 480px;
                    border-radius: 1rem;
                    background-color: #d4d4d8;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    gap: 12px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.10);
                }
                .book-cover-wrapper {
                    flex-shrink: 0;
                    width: 320px;
                }
                .book-info-wrapper {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    gap: 1.25rem;
                }
                .book-layout {
                    display: flex;
                    flex-direction: row;
                    gap: 2.5rem;
                    align-items: flex-start;
                }
            </style>

            {{-- Two-column layout --}}
            <div class="book-layout">

                {{-- LEFT: Cover --}}
                <div class="book-cover-wrapper">
                    @if ($book->coverImage)
                        <img src="{{ Storage::url($book->coverImage) }}"
                             alt="{{ $book->title }}"
                             style="width: 100%; height: 480px; border-radius: 1rem; box-shadow: 0 4px 24px rgba(0,0,0,0.12); object-fit: cover; display: block; background-color: #f4f4f5; text-align: center; line-height: 480px; color: #a1a1aa;">
                    @else
                        <div class="book-cover-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #a1a1aa;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span style="color: #71717a; font-weight: 500; font-size: 14px; text-align: center; padding: 0 16px;">
                {{ $book->title }}
            </span>
                        </div>
                    @endif
                </div>

                {{-- RIGHT: Info --}}
                <div class="book-info-wrapper">

                    {{-- Author & Year --}}
                    <p class="text-zinc-500 text-sm">
                        {{ $book->authorName ?? '—' }}
                        @if ($book->publishYear)
                            · {{ $book->publishYear }}
                        @endif
                    </p>

                    {{-- Rating --}}
                    @if ($book->averageRating > 0)
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= round($book->averageRating) ? 'text-yellow-400' : 'text-zinc-300 dark:text-zinc-600' }}"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm text-zinc-500">
                                {{ number_format($book->averageRating, 1) }}
                                ({{ $book->ratingsCount }} {{ Str::plural('review', $book->ratingsCount) }})
                            </span>
                        </div>
                    @endif

                    {{-- Genres --}}
                    @if (!empty($book->genres))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($book->genres as $genre)
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border border-blue-100 dark:border-blue-900">
                                    {{ $genre->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <hr class="border-zinc-200 dark:border-zinc-700">

                    {{-- Price --}}
                    <span class="text-4xl font-bold text-zinc-900 dark:text-zinc-100">
                        ${{ number_format($book->price, 2) }}
                    </span>

                    {{-- Add to cart --}}
                    @if ($book->stock > 0)
                        <form action="{{ route('cart.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                            <div class="flex items-center gap-3">
                                <input type="number" name="quantity"
                                       value="1" min="1" max="{{ $book->stock }}"
                                       class="w-20 rounded-xl border border-zinc-300 dark:border-zinc-600 px-3 py-2.5 text-sm text-center dark:bg-zinc-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="submit"
                                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                                    </svg>
                                    Add to cart
                                </button>
                            </div>
                        </form>
                        <p class="text-sm text-green-600 dark:text-green-400">
                            In stock ({{ $book->stock }} available)
                        </p>
                    @else
                        <button disabled
                                class="flex items-center gap-2 bg-zinc-200 dark:bg-zinc-700 text-zinc-400 font-semibold px-6 py-2.5 rounded-xl text-sm cursor-not-allowed w-fit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                            Out of stock
                        </button>
                    @endif

                    {{-- Description --}}
                    @if ($book->description)
                        <hr class="border-zinc-200 dark:border-zinc-700">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-2">Description</p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">{{ $book->description }}</p>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

    @include('chat.widget', ['book' => $book])

</x-layouts::app.header>
