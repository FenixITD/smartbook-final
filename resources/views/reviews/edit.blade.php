<x-layouts::app.header title="Edit Review">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <div class="flex items-center gap-4">
            <flux:button href="{{ route('reviews.index') }}" variant="ghost" icon="arrow-left" />
            <div>
                <flux:heading size="xl">Edit review</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Review #{{ $review->id }}</flux:text>
            </div>
        </div>

        <div class="max-w-xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <form method="POST" action="{{ route('reviews.update', $review->id) }}" class="flex flex-col gap-5">
                    @csrf
                    @method('PUT')

                    <flux:field>
                        <flux:label for="userId">User ID</flux:label>
                        <flux:input id="userId" name="userId" type="number" min="1" value="{{ old('userId', $review->userId) }}" :invalid="$errors->has('userId')" />
                        @error('userId') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="bookId">Book ID</flux:label>
                        <flux:input id="bookId" name="bookId" type="number" min="1" value="{{ old('bookId', $review->bookId) }}" :invalid="$errors->has('bookId')" />
                        @error('bookId') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="rating">Rating (0–5)</flux:label>
                        <flux:input id="rating" name="rating" type="number" step="0.1" min="0" max="5" value="{{ old('rating', $review->rating) }}" :invalid="$errors->has('rating')" />
                        @error('rating') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="comment">Comment</flux:label>
                        <flux:textarea id="comment" name="comment" rows="4">{{ old('comment', $review->comment) }}</flux:textarea>
                        @error('comment') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <div class="flex gap-3 pt-2">
                        <flux:button type="submit" variant="primary">Save changes</flux:button>
                        <flux:button href="{{ route('reviews.show', $review->id) }}" variant="ghost">Cancel</flux:button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Danger zone --}}
        <div class="max-w-xl">
            <div class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-900 dark:bg-red-950">
                <flux:heading size="sm" class="text-red-700 dark:text-red-400">Danger zone</flux:heading>
                <flux:text class="mt-1 text-red-600 dark:text-red-400 text-sm">
                    Deleting a review is irreversible.
                </flux:text>
                <div class="mt-4">
                    <div>
                        <flux:modal.trigger name="delete-review-{{ $review->id }}">
                            <flux:button variant="danger" icon="trash">Delete review</flux:button>
                        </flux:modal.trigger>

                        <flux:modal name="delete-review-{{ $review->id }}" class="min-w-[22rem]">
                            <flux:heading size="lg">Delete review?</flux:heading>
                            <flux:subheading>Are you sure you want to delete review #{{ $review->id }}? This action cannot be undone.</flux:subheading>

                            <div class="flex gap-2 mt-6 justify-end">
                                <flux:modal.close>
                                    <flux:button variant="ghost">Cancel</flux:button>
                                </flux:modal.close>

                                <form action="{{ route('reviews.destroy', $review->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button type="submit" variant="danger">Delete review</flux:button>
                                </form>
                            </div>
                        </flux:modal>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts::app.header>
