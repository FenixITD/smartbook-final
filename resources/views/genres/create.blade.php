<x-layouts::app.sidebar title="Create Genre">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        {{-- Header --}}
        <div class="flex items-center gap-4">
            <flux:button href="{{ route('genres.index') }}" variant="ghost" icon="arrow-left" />
            <div>
                <flux:heading size="xl">New genre</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Fill in the genre details</flux:text>
            </div>
        </div>

        {{-- Form --}}
        <div class="max-w-xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <form method="POST" action="{{ route('genres.store') }}" class="flex flex-col gap-5">
                    @csrf

                    <flux:field>
                        <flux:label for="name">Genre name</flux:label>
                        <flux:input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
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
                            value="{{ old('slug') }}"
                            placeholder="e.g. science-fiction"
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
                            :invalid="$errors->has('description')">{{ old('description') }}
                        </flux:textarea>
                        @error('description')
                        <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    <div class="flex gap-3 pt-2">
                        <flux:button type="submit" variant="primary">
                            Create genre
                        </flux:button>
                        <flux:button href="{{ route('genres.index') }}" variant="ghost">
                            Cancel
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts::app.sidebar>
