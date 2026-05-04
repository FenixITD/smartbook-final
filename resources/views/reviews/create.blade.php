<x-layouts::app.header title="Create Review">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <div class="flex items-center gap-4">
            <flux:button href="{{ route('reviews.index') }}" variant="ghost" icon="arrow-left" />
            <div>
                <flux:heading size="xl">New review</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Fill in the review details</flux:text>
            </div>
        </div>

        <div class="max-w-xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <form method="POST" action="{{ route('reviews.store') }}" class="flex flex-col gap-5">
                    @csrf

                    <flux:field>
                        <flux:label for="userId">User ID</flux:label>
                        <flux:input id="userId" name="userId" type="number" min="1" value="{{ old('userId') }}" :invalid="$errors->has('userId')" />
                        @error('userId') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="bookId">Book ID</flux:label>
                        <flux:input id="bookId" name="bookId" type="number" min="1" value="{{ old('bookId') }}" :invalid="$errors->has('bookId')" />
                        @error('bookId') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="rating">Rating (0–5)</flux:label>
                        <flux:input id="rating" name="rating" type="number" step="0.1" min="0" max="5" value="{{ old('rating') }}" :invalid="$errors->has('rating')" />
                        @error('rating') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label for="comment">Comment</flux:label>
                        <flux:textarea id="comment" name="comment" rows="4" :invalid="$errors->has('comment')">{{ old('comment') }}</flux:textarea>
                        @error('comment') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <div class="flex gap-3 pt-2">
                        <flux:button type="submit" variant="primary">Create review</flux:button>
                        <flux:button href="{{ route('reviews.index') }}" variant="ghost">Cancel</flux:button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts::app.header>
