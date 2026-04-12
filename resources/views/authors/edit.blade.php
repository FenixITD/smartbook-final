<x-layouts::app.sidebar title="Edit Author">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        {{-- Header --}}
        <div class="flex items-center gap-4">
            <flux:button href="{{ route('authors.index') }}" variant="ghost" icon="arrow-left" />
            <div>
                <flux:heading size="xl">Edit author</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ $author->name }}</flux:text>
            </div>
        </div>

        {{-- Form --}}
        <div class="max-w-xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <form method="POST" action="{{ route('authors.update', $author) }}" class="flex flex-col gap-5">
                    @csrf
                    @method('PUT')

                    <flux:field>
                        <flux:label for="name">Author name</flux:label>
                        <flux:input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $author->name) }}"
                            placeholder="e.g. Leo Tolstoy"
                            autofocus=""
                            :invalid="$errors->has('name')"
                        />
                        @error('name')
                        <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <div class="flex gap-3 pt-2">
                        <flux:button type="submit" variant="primary">
                            Save changes
                        </flux:button>
                        <flux:button href="{{ route('authors.show', $author) }}" variant="ghost">
                            Cancel
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Danger zone --}}
        <div class="max-w-xl">
            <div class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-900 dark:bg-red-950">
                <flux:heading size="sm" class="text-red-700 dark:text-red-400">Danger zone</flux:heading>
                <flux:text class="mt-1 text-red-600 dark:text-red-400 text-sm">
                    Deleting an author is irreversible. All related data will be lost.
                </flux:text>
                <div class="mt-4">
                    <form action="{{ route('authors.destroy', $author) }}" method="POST"
                          onsubmit="return confirm('Delete author \'{{ $author->name }}\'? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="danger" icon="trash">
                            Delete author
                        </flux:button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layouts::app.sidebar>
