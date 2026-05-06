{{--
    Виджет чата на странице книги.
    Подключается в catalog/show.blade.php как @include('chat.widget', ['book' => $book])

    Alpine.js управляет состоянием:
    - open      — открыт ли чат
    - loading   — идёт ли загрузка
    - messages  — массив сообщений
    - body      — текст в поле ввода
    - conversationId — id диалога (получаем с сервера)

    x-data    — объявляет Alpine-компонент и его данные
    x-show    — показывает/скрывает элемент (display:none)
    @click    — обработчик клика
    x-model   — двусторонняя привязка данных (как v-model в Vue)
    x-for     — цикл
    x-ref     — ссылка на DOM-элемент (как ref в Vue)
--}}

@auth
    <div
        x-data="{
        open: false,
        loading: false,
        sending: false,
        messages: [],
        body: '',
        conversationId: null,

        {{-- Открываем чат: запрашиваем/создаём диалог, загружаем историю --}}
        async openChat() {
            this.open = true;
            if (this.conversationId) return; {{-- Уже загружен --}}
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
                this.messages = data.messages;
                this.scrollToBottom();
                this.subscribeToChannel(); {{-- Подписываемся на Reverb --}}
            } finally {
                this.loading = false;
            }
        },

        {{-- Отправляем сообщение --}}
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
                {{-- Optimistic UI: добавляем сообщение сразу, не дожидаясь Reverb --}}
                const msg = await res.json();
                this.messages.push(msg);
                this.scrollToBottom();
            } finally {
                this.sending = false;
            }
        },

        {{-- Подписка на WebSocket-канал через Laravel Echo + Reverb --}}
        subscribeToChannel() {
            {{--
                window.Echo — глобальный объект Laravel Echo.
                private() — подписка на приватный канал (авторизация через channels.php).
                .listen('.MessageSent', ...) — слушаем событие MessageSent.
                Точка перед именем события важна — она отключает namespace.
            --}}
            window.Echo.private(`conversation.${this.conversationId}`)
                .listen('.MessageSent', (event) => {
                    {{-- Не добавляем дубликаты (мы уже добавили своё сообщение выше) --}}
                    const exists = this.messages.some(m => m.id === event.id);
                    if (!exists) {
                        this.messages.push(event);
                        this.scrollToBottom();
                    }
                });
        },

        {{-- Прокручиваем окно чата вниз --}}
        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messageList;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
    }"
        class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3"
    >
        {{-- Окно чата --}}
        <div
            x-show="open"
            x-transition
            class="w-80 sm:w-96 bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-700 flex flex-col overflow-hidden"
            style="max-height: 480px; display: none;"
        >
            {{-- Заголовок --}}
            <div class="flex items-center justify-between px-4 py-3 bg-blue-600 text-white">
                <div>
                    <p class="font-semibold text-sm">Чат с администратором</p>
                    <p class="text-xs opacity-75 truncate">{{ $book->title }}</p>
                </div>
                <button @click="open = false" class="hover:opacity-75 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Список сообщений --}}
            <div x-ref="messageList" class="flex-1 overflow-y-auto p-4 space-y-3 bg-zinc-50 dark:bg-zinc-950">

                {{-- Спиннер загрузки --}}
                <div x-show="loading" class="flex justify-center py-6">
                    <svg class="animate-spin h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                {{-- Пустой чат --}}
                <template x-if="!loading && messages.length === 0">
                    <p class="text-center text-zinc-400 text-sm py-4">Задайте ваш вопрос об этой книге!</p>
                </template>

                {{-- Сообщения --}}
                {{-- x-for требует :key для оптимального обновления DOM --}}
                <template x-for="msg in messages" :key="msg.id">
                    <div :class="msg.user_id === {{ auth()->id() }} ? 'flex justify-end' : 'flex justify-start'">
                        <div
                            :class="msg.user_id === {{ auth()->id() }}
                            ? 'bg-blue-600 text-white rounded-2xl rounded-br-sm'
                            : 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700 rounded-2xl rounded-bl-sm'"
                            class="max-w-[75%] px-3 py-2 text-sm shadow-sm"
                        >
                            {{-- Имя отправителя (только для чужих сообщений) --}}
                            <p x-show="msg.user_id !== {{ auth()->id() }}"
                               class="text-xs font-semibold mb-1 text-blue-600 dark:text-blue-400"
                               x-text="msg.sender_name"></p>
                            <p x-text="msg.body" class="break-words"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Поле ввода --}}
            <div class="px-3 py-3 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                <div class="flex gap-2">
                    <input
                        x-model="body"
                        @keydown.enter.prevent="send()"
                        placeholder="Напишите сообщение..."
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

        {{-- Кнопка открытия чата --}}
        <button
            @click="openChat()"
            class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center transition-transform hover:scale-105"
            title="Чат с администратором"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </button>
    </div>
@endauth
