<x-layouts::app.header title="Edit Genre">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        {{-- Header --}}
        <div class="flex items-center gap-4">
            <flux:button href="{{ route('genres.index') }}" variant="ghost" icon="arrow-left" />
            <div>
                <flux:heading size="xl">Edit genre</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ $genre->name }}</flux:text>
            </div>
        </div>

        {{-- Form --}}
        <div class="max-w-xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <form method="POST" action="{{ route('genres.update', $genre) }}" class="flex flex-col gap-5">
                    @csrf
                    @method('PUT')

                    <flux:field>
                        <flux:label for="name">Genre name</flux:label>
                        <flux:input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $genre->name) }}"
                            placeholder="e.g. Science Fiction"
                            autofocus
                            :invalid="$errors->has('name')"
                        />
                        @error('name')
                        <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="slug">Slug</flux:label>
                        <flux:input
                            id="slug"
                            name="slug"
                            value="{{ old('slug', $genre->slug) }}"
                            :invalid="$errors->has('slug')" />
                        @error('slug')
                        <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="description">Description</flux:label>
                        <flux:textarea
                            id="description"
                            name="description"
                            rows="3"
                            :invalid="$errors->has('description')">{{ old('description', $genre->description) }}
                        </flux:textarea>
                        @error('description')
                        <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <div class="flex gap-3 pt-2">
                        <flux:button type="submit" variant="primary">
                            Save changes
                        </flux:button>
                        <flux:button href="{{ route('genres.show', $genre) }}" variant="ghost">
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
                    Deleting a genre is irreversible. All related data will be lost.
                </flux:text>
                <div class="mt-4">
                    <div>
                        <flux:modal.trigger name="delete-genre-{{ $genre->id }}">
                            <flux:button variant="danger" icon="trash">Delete genre</flux:button>
                        </flux:modal.trigger>

                        <flux:modal name="delete-genre-{{ $genre->id }}" class="min-w-[22rem]">
                            <flux:heading size="lg">Delete genre?</flux:heading>
                            <flux:subheading>Are you sure you want to delete "{{ $genre->name }}"? This action cannot be undone and all related data will be lost.</flux:subheading>

                            <div class="flex gap-2 mt-6 justify-end">
                                <flux:modal.close>
                                    <flux:button variant="ghost">Cancel</flux:button>
                                </flux:modal.close>

                                <form action="{{ route('genres.destroy', $genre) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button type="submit" variant="danger">Delete genre</flux:button>
                                </form>
                            </div>
                        </flux:modal>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts::app.header>
