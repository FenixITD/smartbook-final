<x-layouts::app.header title="Create Book">
    <div class="flex min-h-screen flex-col">
        <div class="flex-1 flex flex-col gap-6 p-6">

            <div class="flex items-center gap-4">
                <flux:button href="{{ route('books.index') }}" variant="ghost" icon="arrow-left" />
                <div>
                    <flux:heading size="xl">New book</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">Fill in the book details</flux:text>
                </div>
            </div>

            <div class="max-w-2xl">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <form method="POST" action="{{ route('books.store') }}" enctype="multipart/form-data" class="flex flex-col gap-5">
                        @csrf

                        <div class="grid grid-cols-2 gap-4">
                            <flux:field class="col-span-2">
                                <flux:label for="title">Title</flux:label>
                                <flux:input id="title" name="title" value="{{ old('title') }}" :invalid="$errors->has('title')" />
                                @error('title') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label for="slug">Slug</flux:label>
                                <flux:input id="slug" name="slug" value="{{ old('slug') }}" placeholder="my-book-slug" :invalid="$errors->has('slug')" />
                                @error('slug') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="authorId">Author</flux:label>
                                <flux:select id="authorId" name="authorId" :invalid="$errors->has('authorId')">
                                    <flux:select.option value="">Select author...</flux:select.option>
                                    @foreach ($authors as $author)
                                        <flux:select.option value="{{ $author->id }}" :selected="old('authorId') == $author->id">
                                            {{ $author->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                @error('authorId') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="status">Status</flux:label>
                                <flux:select id="status" name="status">
                                    <flux:select.option value="active" :selected="old('active') === 'active'">Active</flux:select.option>
                                    <flux:select.option value="draft" :selected="old('status') === 'draft'">Draft</flux:select.option>
                                    <flux:select.option value="archived" :selected="old('status') === 'archived'">Archived</flux:select.option>
                                </flux:select>
                                @error('status') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="price">Price ($)</flux:label>
                                <flux:input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price') }}" :invalid="$errors->has('price')" />
                                @error('price') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="stock">Stock</flux:label>
                                <flux:input id="stock" name="stock" type="number" min="0" value="{{ old('stock', 0) }}" :invalid="$errors->has('stock')" />
                                @error('stock') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="publishYear">Publish year</flux:label>
                                <flux:input id="publishYear" name="publishYear" type="number" min="1900" max="{{ (int) date('Y') + 1 }}" value="{{ old('publishYear') }}" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="cover_image">Cover image</flux:label>
                                <input id="cover_image" name="cover_image" type="file" accept="image/*"
                                       class="block w-full text-sm text-zinc-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-950 dark:file:text-blue-300" />
                                @error('cover_image') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label for="description">Description</flux:label>
                                <flux:textarea id="description" name="description" rows="4" :invalid="$errors->has('description')">{{ old('description') }}</flux:textarea>
                                @error('description') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label>Genres</flux:label>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @foreach ($genres as $genre)
                                        <label class="flex items-center gap-2 cursor-pointer px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                            <input type="checkbox" name="genres[]" value="{{ $genre->id }}"
                                                   {{ in_array($genre->id, old('genres', [])) ? 'checked' : '' }}
                                                   class="rounded border-zinc-300 text-blue-600">
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $genre->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </flux:field>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <flux:button type="submit" variant="primary">Create book</flux:button>
                            <flux:button href="{{ route('books.index') }}" variant="ghost">Cancel</flux:button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-layouts::app.header>
