<x-layouts::app.header title="Authors">
    <div class="flex min-h-screen flex-col">

        {{-- Основной контент --}}
        <div class="flex-1 flex flex-col gap-6 p-6">

            {{-- Header (заголовок страницы + кнопка) --}}
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">Authors</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Manage book authors</flux:text>
                </div>
                <flux:button href="{{ route('authors.create') }}" variant="primary" icon="plus">
                    Add author
                </flux:button>
            </div>

            {{-- Flash message --}}
            @if (session('success'))
                <flux:callout variant="success" icon="check-circle">
                    {{ session('success') }}
                </flux:callout>
            @endif

            {{-- Filters --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <form method="GET" action="{{ route('authors.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-48">
                        <flux:input
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by name..."
                            icon="magnifying-glass"
                        />
                    </div>
                    <div class="w-40">
                        <flux:select name="sortBy">
                            <flux:select.option value="id" :selected="request('sortBy') === 'id'">By ID</flux:select.option>
                            <flux:select.option value="name" :selected="request('sortBy') === 'name'">By name</flux:select.option>
                            <flux:select.option value="created_at" :selected="request('sortBy') === 'created_at'">By date</flux:select.option>
                        </flux:select>
                    </div>
                    <div class="w-40">
                        <flux:select name="sortDirection">
                            <flux:select.option value="asc" :selected="request('sortDirection') === 'asc'">Ascending</flux:select.option>
                            <flux:select.option value="desc" :selected="request('sortDirection') === 'desc'">Descending</flux:select.option>
                        </flux:select>
                    </div>
                    <flux:button type="submit" variant="filled">Apply</flux:button>
                    @if(request('search') || request('sortBy'))
                        <flux:button href="{{ route('authors.index') }}" variant="ghost">Reset</flux:button>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-700 dark:bg-zinc-900 flex-1 flex flex-col">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-12">
                                #
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Created at
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($authors as $author)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                <td class="px-6 py-4 text-sm text-zinc-400 dark:text-zinc-500">
                                    {{ $author->id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xs font-bold dark:bg-blue-900 dark:text-blue-300">
                                            {{ mb_strtoupper(mb_substr($author->name, 0, 1)) }}
                                        </div>
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $author->name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $author->created_at->format('d.m.Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <flux:button href="{{ route('authors.show', $author) }}" variant="ghost" size="sm" icon="eye">
                                            View
                                        </flux:button>
                                        <flux:button href="{{ route('authors.edit', $author) }}" variant="ghost" size="sm" icon="pencil">
                                            Edit
                                        </flux:button>
                                        <form action="{{ route('authors.destroy', $author) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Delete author \'{{ $author->name }}\'?')">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" variant="ghost" size="sm" icon="trash">
                                                Delete
                                            </flux:button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-zinc-400">
                                        <flux:icon name="user-group" class="w-10 h-10" />
                                        <span class="text-sm">No authors yet</span>
                                        <flux:button href="{{ route('authors.create') }}" variant="primary" size="sm">
                                            Add first author
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($authors->hasPages())
                    <div class="border-t border-zinc-200 dark:border-zinc-700 px-6 py-4 mt-auto">
                        {{ $authors->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>

            <flux:text class="text-zinc-400 text-sm">
                Total authors: {{ $authors->total() }}
            </flux:text>

        </div>

        {{-- Тестовый футер --}}
        <footer class="bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 py-4 px-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
            <p>© {{ date('Y') }} Your Book Library. All rights reserved.</p>
            <p class="mt-1">Built with Laravel + Flux UI • Test version</p>
        </footer>

    </div>
</x-layouts::app.header>
