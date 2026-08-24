<x-layouts::app.header title="Edit Book">
    <div class="flex min-h-screen flex-col">
        <div class="flex-1 flex flex-col gap-6 p-6">

            <div class="flex items-center gap-4">
                <flux:button href="{{ route('books.index') }}" variant="ghost" icon="arrow-left" />
                <div>
                    <flux:heading size="xl">Edit book</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">{{ $book->title }}</flux:text>
                </div>
            </div>

            <div class="max-w-2xl">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <form method="POST" action="{{ route('books.update', $book->id) }}" enctype="multipart/form-data" class="flex flex-col gap-5">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-2 gap-4">
                            <flux:field class="col-span-2">
                                <flux:label for="title">Title</flux:label>
                                <flux:input id="title" name="title" value="{{ old('title', $book->title) }}" :invalid="$errors->has('title')" />
                                @error('title') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label for="slug">Slug</flux:label>
                                <flux:input id="slug" name="slug" value="{{ old('slug', $book->slug) }}" :invalid="$errors->has('slug')" />
                                @error('slug') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="authorId">Author</flux:label>
                                <flux:select id="authorId" name="authorId">
                                    @foreach ($authors as $author)
                                        <flux:select.option value="{{ $author->id }}" :selected="old('authorId', $book->authorId) == $author->id">
                                            {{ $author->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                @error('authorId') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="status">Status</flux:label>
                                <flux:select id="status" name="status">
                                    <flux:select.option value="active" :selected="old('status', $book->status) === 'active'">Active</flux:select.option>
                                    <flux:select.option value="draft" :selected="old('status', $book->status) === 'draft'">Draft</flux:select.option>
                                    <flux:select.option value="archived" :selected="old('status', $book->status) === 'archived'">Archived</flux:select.option>
                                </flux:select>
                            </flux:field>

                            <flux:field>
                                <flux:label for="price">Price ($)</flux:label>
                                <flux:input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $book->price) }}" :invalid="$errors->has('price')" />
                                @error('price') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label for="stock">Stock</flux:label>
                                <flux:input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $book->stock) }}" :invalid="$errors->has('stock')" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="publishYear">Publish year</flux:label>
                                <flux:input id="publishYear" name="publishYear" type="number" value="{{ old('publishYear', $book->publishYear) }}" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="cover_image">Cover image</flux:label>
                                @if ($book->coverImage)
                                    <div class="mb-2 flex items-center gap-3">
                                        <img src="{{ Storage::disk('s3')->url($book->coverImage) }}" alt="Current cover"
                                             class="w-12 h-16 object-cover rounded-lg border border-zinc-200">
                                        <span class="text-xs text-zinc-400">Current cover</span>
                                    </div>
                                @endif
                                <input id="cover_image" name="cover_image" type="file" accept="image/*"
                                       class="block w-full text-sm text-zinc-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                <p class="text-xs text-zinc-400 mt-1">Leave empty to keep current cover</p>
                                @error('cover_image') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label for="description">Description</flux:label>
                                <flux:textarea id="description" name="description" rows="4">{{ old('description', $book->description) }}</flux:textarea>
                                @error('description') <flux:error>{{ $message }}</flux:error> @enderror
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label>Genres</flux:label>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @php $selectedGenres = old('genres', array_column(array_map(fn($g) => ['id' => $g->id], $book->genres), 'id')); @endphp
                                    @foreach ($genres as $genre)
                                        <label class="flex items-center gap-2 cursor-pointer px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                            <input type="checkbox" name="genres[]" value="{{ $genre->id }}"
                                                   {{ in_array($genre->id, $selectedGenres) ? 'checked' : '' }}
                                                   class="rounded border-zinc-300 text-blue-600">
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $genre->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </flux:field>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <flux:button type="submit" variant="primary">Save changes</flux:button>
                            <flux:button href="{{ route('books.show', $book->id) }}" variant="ghost">Cancel</flux:button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Danger zone --}}
            <div class="max-w-2xl">
                <div class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-900 dark:bg-red-950">
                    <flux:heading size="sm" class="text-red-700 dark:text-red-400">Danger zone</flux:heading>
                    <flux:text class="mt-1 text-red-600 dark:text-red-400 text-sm">Deleting a book is irreversible.</flux:text>
                    <div class="mt-4">
                        <div>
                            <flux:modal.trigger name="delete-book-{{ $book->id }}">
                                <flux:button variant="danger" icon="trash">Delete book</flux:button>
                            </flux:modal.trigger>

                            <flux:modal name="delete-book-{{ $book->id }}" class="min-w-[22rem]">
                                <flux:heading size="lg">Delete book?</flux:heading>
                                <flux:subheading>Are you sure you want to delete "{{ $book->title }}"? This action cannot be undone.</flux:subheading>

                                <div class="flex gap-2 mt-6 justify-end">
                                    <flux:modal.close>
                                        <flux:button variant="ghost">Cancel</flux:button>
                                    </flux:modal.close>

                                    <form action="{{ route('books.destroy', $book->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" variant="danger">Delete book</flux:button>
                                    </form>
                                </div>
                            </flux:modal>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layouts::app.header>
