<x-layouts::app.header title="Chat">
    <div class="flex min-h-screen flex-col">
        <div class="flex-1 flex flex-col gap-6 p-6">

            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">Dialogues</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">User questions about books</flux:text>
                </div>
            </div>

            {{-- Empty state --}}
            @if (empty($conversations))
                <div class="flex flex-col items-center justify-center rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-20 gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-zinc-400 text-sm">There is no dialogues yet</p>
                </div>
            @else

                {{-- Table --}}
                <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">

                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-12">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Book</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Last message</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Updated</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>

                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($conversations as $conversation)
                                @php $lastMessage = $conversation->lastMessageBody; @endphp
                                <tr
                                    x-data="{ unread: {{ $conversation->unreadCount }}, status: '{{ $conversation->status }}' }"
                                    @unread-cleared.window="
                                        if ($event.detail.conversationId === {{ $conversation->id }} && unread > 0) {
                                            $dispatch('admin-unread-decreased', { by: unread });
                                            unread = 0;
                                        }
                                    "
                                    @conversation-closed.window="
                                        if ($event.detail.conversationId === {{ $conversation->id }}) status = 'closed'
                                    "
                                    class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors"
                                >
                                    {{-- ID --}}
                                    <td class="px-6 py-4 text-sm text-zinc-400 dark:text-zinc-500">
                                        {{ $conversation->id }}
                                    </td>

                                    {{-- User --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $conversation->userName }}
                                            </span>
                                            <span
                                                x-show="unread > 0"
                                                x-text="unread"
                                                class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-bold"
                                            ></span>
                                        </div>
                                        @if ($conversation->userEmail)
                                            <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                                {{ $conversation->userEmail }}
                                            </p>
                                        @endif
                                    </td>

                                    {{-- Book --}}
                                    <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400 max-w-45 truncate">
                                        {{ $conversation->bookTitle }}
                                    </td>

                                    {{-- Preview of the last message --}}
                                    <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400 max-w-55">
                                        @if ($lastMessage)
                                            <p class="truncate">{{ $lastMessage }}</p>
                                        @else
                                            <span class="text-zinc-300 dark:text-zinc-600 italic">No messages</span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-4">
                                        <span
                                            x-show="status === 'open'"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-400"
                                        >Opened</span>
                                        <span
                                            x-show="status !== 'open'"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-red-600 dark:bg-zinc-800 dark:text-red-300"
                                        >Closed</span>
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $conversation->updatedAt }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-4 text-right">
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="chat-bubble-left-right"
                                            x-data=""
                                            @click="$dispatch('open-admin-chat', {
                                                conversationId: {{ $conversation->id }},
                                                userName: '{{ addslashes($conversation->userName) }}',
                                                bookTitle: '{{ addslashes($conversation->bookTitle) }}'
                                            })"
                                        >
                                            Open
                                        </flux:button>
                                    </td>

                                </tr>
                            @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>

            @endif

        </div>
    </div>

    {{-- Modal chat window for the administrator --}}
    <div
        x-data="{
            open: false,
            loading: false,
            sending: false,
            conversationId: null,
            userName: '',
            bookTitle: '',
            messages: [],
            body: '',

            init() {
                this.$watch('open', (value) => {
                    document.body.style.overflow = value ? 'hidden' : '';
                });

                window.addEventListener('open-admin-chat', (e) => {
                    const data = e.detail;
                    this.conversationId = data.conversationId;
                    this.userName       = data.userName;
                    this.bookTitle      = data.bookTitle;
                    this.messages       = [];
                    this.body           = '';
                    this.open           = true;
                    this.loadMessages();
                });
            },

            async loadMessages() {
                this.loading = true;
                try {
                    const res = await fetch(`/chat/conversation/${this.conversationId}/messages`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    this.messages = data.messages;
                    this.scrollToBottom();
                    this.subscribeToChannel();
                    this.$dispatch('unread-cleared', { conversationId: this.conversationId });
                } finally {
                    this.loading = false;
                }
            },

            async send() {
                if (!this.body.trim() || this.sending) return;

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
                        console.error('Error sending message:', await res.text());
                        return;
                    }

                    const msg = await res.json();
                    const exists = this.messages.some(m => String(m.id) === String(msg.id));
                    if (!exists) {
                        this.messages.push(msg);
                        this.scrollToBottom();
                    }
                } catch (error) {
                    console.error('Network error:', error);
                } finally {
                    this.sending = false;
                }
            },

            async closeConversation() {
                await fetch(`/chat/conversation/${this.conversationId}/close`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });
                this.$dispatch('conversation-closed', { conversationId: this.conversationId });
                this.close();
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
                    const el = this.$refs.adminMessageList;
                    if (el) el.scrollTop = el.scrollHeight;
                });
            },

            close() {
                this.open = false;
                if (this.conversationId) {
                    window.Echo.leave(`conversation.${this.conversationId}`);
                }
            },
        }"
        x-show="open"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        style="display: none;"
        @keydown.escape.window="close()"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-700 flex flex-col w-full max-w-lg" style="height: 560px;">

            {{-- Modal window title --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <div>
                    <p class="font-semibold text-zinc-900 dark:text-zinc-100" x-text="userName"></p>
                    <p class="text-xs text-zinc-400 truncate max-w-xs" x-text="bookTitle"></p>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Close conversation button --}}
                    <button
                        @click="closeConversation()"
                        class="text-xs text-zinc-400 hover:text-red-500 border border-zinc-200 dark:border-zinc-600 rounded-lg px-2 py-1 transition"
                        title="Close conversation"
                    >
                        Close
                    </button>
                    {{-- Dismiss modal button --}}
                    <button @click="close()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- List of messages --}}
            <div x-ref="adminMessageList" class="flex-1 overflow-y-auto p-4 space-y-3 bg-zinc-50 dark:bg-zinc-950">

                <div x-show="loading" class="flex justify-center py-6">
                    <svg class="animate-spin h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                <template x-if="!loading && messages.length === 0">
                    <p class="text-center text-zinc-400 text-sm py-4">There are no messages yet</p>
                </template>

                <template x-for="msg in messages" :key="'msg-' + msg.id">
                    <div :class="msg.user_id === {{ auth()->id() }} ? 'flex justify-end' : 'flex justify-start'">
                        <div
                            :class="msg.user_id === {{ auth()->id() }}
                                ? 'bg-blue-600 text-white rounded-2xl rounded-br-sm'
                                : 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700 rounded-2xl rounded-bl-sm'"
                            class="max-w-75% px-3 py-2 text-sm shadow-sm"
                        >
                            <p class="text-xs font-semibold mb-1 opacity-60" x-text="msg.sender_name"></p>
                            <p x-text="msg.body" class="wrap-break-word"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Input field --}}
            <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                <div class="flex gap-2">
                    <input
                        x-model="body"
                        @keydown.enter.prevent="send()"
                        placeholder="Reply to user..."
                        class="flex-1 rounded-xl border border-zinc-300 dark:border-zinc-600 px-3 py-2 text-sm dark:bg-zinc-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :disabled="sending || loading"
                    />
                    <button
                        @click="send()"
                        :disabled="!body.trim() || sending || loading"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-zinc-300 dark:disabled:bg-zinc-700 text-white rounded-xl px-3 py-2 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

</x-layouts::app.header>
