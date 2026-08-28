@auth
    <div
        x-data="{
        open: false,
        loading: false,
        sending: false,
        closed: false,
        messages: [],
        body: '',
        conversationId: null,

        async openChat() {
            this.open = true;
            if (this.conversationId) return;
            this.loading = true;
            try {
                const res = await fetch('{{ route('chat.open', $book->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });
                const data = await res.json();
                this.conversationId = data.conversation_id;
                this.closed = data.status === 'closed';
                this.messages = data.messages;
                this.scrollToBottom();
                this.subscribeToChannel();
            } finally {
                this.loading = false;
            }
        },

        async send() {
            if (!this.body.trim() || this.sending || this.closed) return;

            this.sending = true;
            const text = this.body;
            this.body = '';

            try {
                const res = await fetch(`/chat/conversation/${this.conversationId}/messages`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ body: text }),
                });

                if (!res.ok) {
                    if (res.status === 403) {
                        this.closed = true;
                        this.body = '';
                        return;
                    }

                    this.body = text;
                    return;
                }

                const msg = await res.json();
                const exists = this.messages.some(m => String(m.id) === String(msg.id));

                if (!exists) {
                    this.messages.push(msg);
                    this.scrollToBottom();
                }
            } catch (error) {
                this.body = text;
            } finally {
                this.sending = false;
            }
        },

        subscribeToChannel() {
            window.Echo.private(`conversation.${this.conversationId}`)
                .listen('.MessageSent', (event) => {
                    const incomingMsg = event.id ? event : (event.message || event);

                    if (!incomingMsg || !incomingMsg.id) return;

                    const exists = this.messages.some(m => String(m.id) === String(incomingMsg.id));

                    if (!exists) {
                        this.messages.push(incomingMsg);
                        this.scrollToBottom();
                    }
                });
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messageList;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
    }"
        class="fixed z-50 flex flex-col items-end gap-3" style="bottom: 1.5rem; right: 1.5rem;"
    >
        <div
            x-show="open"
            x-transition
            class="w-80 sm:w-96 bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-700 flex flex-col overflow-hidden"
            style="max-height: 480px; display: none;"
        >
            <div class="flex items-center justify-between px-4 py-3 bg-blue-600 text-white">
                <div>
                    <p class="font-semibold text-sm">Chat with the administrator</p>
                    <p class="text-xs opacity-75 truncate">{{ $book->title }}</p>
                </div>
                <button @click="open = false" class="hover:opacity-75 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div x-ref="messageList" class="flex-1 overflow-y-auto p-4 space-y-3 bg-zinc-50 dark:bg-zinc-950">

                <div x-show="loading" class="flex justify-center py-6">
                    <svg class="animate-spin h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                <template x-if="!loading && messages.length === 0">
                    <p class="text-center text-zinc-400 text-sm py-4">Ask your question about this book</p>
                </template>

                <template x-for="msg in messages" :key="'msg-' + msg.id">
                    <div :class="msg.user_id === {{ auth()->id() }} ? 'flex justify-end' : 'flex justify-start'">
                        <div
                            :class="msg.user_id === {{ auth()->id() }}
                            ? 'bg-blue-600 text-white rounded-2xl rounded-br-sm'
                            : 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700 rounded-2xl rounded-bl-sm'"
                            class="max-w-75% px-3 py-2 text-sm shadow-sm"
                        >
                            <p x-show="msg.user_id !== {{ auth()->id() }}"
                               class="text-xs font-semibold mb-1 text-blue-600 dark:text-blue-400"
                               x-text="msg.sender_name"></p>
                            <p x-text="msg.body" class="wrap-break-word"></p>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="closed" class="px-3 py-2.5 border-t border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950">
                <p class="text-xs font-medium text-amber-700 dark:text-amber-400 flex items-start gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    This conversation was closed by the administrator. You can no longer send messages here.
                </p>
            </div>

            <div class="px-3 py-3 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                <div class="flex gap-2">
                    <input
                        x-model="body"
                        @keydown.enter.prevent="!closed && send()"
                        placeholder="Write a message..."
                        class="flex-1 rounded-xl border border-zinc-300 dark:border-zinc-600 px-3 py-2 text-sm dark:bg-zinc-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                        :disabled="sending || loading || closed"
                    />
                    <button
                        @click="send()"
                        :disabled="!body.trim() || sending || loading || closed"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-zinc-300 dark:disabled:bg-zinc-700 text-white rounded-xl px-3 py-2 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <button
            @click="openChat()"
            class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center transition-transform hover:scale-105"
            title="Chat with the administrator"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </button>
    </div>
@endauth
