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
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $book->status === 'active' ? 'bg-green-100 text-green-700' : ($book->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-zinc-100 text-zinc-600') }}">
                                        {{ ucfirst($book->status) }}
                                    </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <flux:button href="{{ route('books.show', $book) }}" variant="ghost" size="sm" icon="eye">View</flux:button>
                                    <flux:button href="{{ route('books.edit', $book) }}" variant="ghost" size="sm" icon="pencil">Edit</flux:button>
                                    <form action="{{ route('books.destroy', $book) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Delete book \'{{ $book->title }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" variant="ghost" size="sm" icon="trash">Delete</flux:button>
                                    </form>
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
</x-layouts::app.header>
