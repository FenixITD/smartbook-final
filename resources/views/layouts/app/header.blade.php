<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title></title>@include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
<flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

    <flux:navbar class="-mb-px max-lg:hidden">
        <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
            {{ __('Dashboard') }}
        </flux:navbar.item>
        <flux:navbar.item icon="cog-6-tooth" :href="route('books.index')" :current="request()->routeIs('books.*', 'authors.*', 'genres.*', 'orders.*', 'reviews.*')" wire:navigate>
            {{ __('Admin panel') }}
        </flux:navbar.item>
    </flux:navbar>

    <flux:spacer />

    {{-- Search with autocomplete --}}
    <div class="max-lg:hidden" x-data="searchAutocomplete()" x-ref="wrapper">
        <form method="GET" action="{{ route('dashboard') }}" x-on:submit.prevent="submitForm">
            <flux:input
                name="search"
                x-model="query"
                x-on:input.debounce.300ms="fetchSuggestions"
                x-on:keydown.escape="close"
                x-on:focus="query.trim().length >= 2 && fetchSuggestions()"
                value="{{ request('search') }}"
                placeholder="Search books..."
                icon="magnifying-glass"
                class="w-72"
                autocomplete="off"
            />
        </form>

        {{-- Dropdown teleported to <body> — escapes overflow:hidden in header --}}
        {{-- x-teleport preserves the parent x-data scope in Alpine v3 --}}
        <template x-teleport="body">
            <div
                x-show="open || loading"
                :style="'position:fixed;z-index:9999;top:'+dropY+'px;left:'+dropX+'px;width:'+dropW+'px'"
                class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
            >
                <div x-show="loading" class="px-4 py-3 text-sm text-zinc-400 text-center">
                    Searching...
                </div>

                <ul x-show="!loading && open">
                    <template x-for="book in suggestions" :key="book.id">
                        <li>
                            <a :href="book.url"
                               x-on:click.prevent="selectBook(book)"
                               class="flex items-center gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                                <div class="flex-shrink-0 w-10 h-14 rounded overflow-hidden bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                    <img x-show="book.cover_image" :src="book.cover_image" :alt="book.title" class="w-full h-full object-cover"  alt="" src=""/>
                                    <svg x-show="!book.cover_image" class="w-5 h-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate" x-text="book.title"></p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5" x-text="book.author"></p>
                                </div>
                                <span class="flex-shrink-0 text-sm font-semibold text-zinc-700 dark:text-zinc-300" x-text="'$' + Number(book.price).toFixed(2)"></span>
                            </a>
                        </li>
                    </template>
                </ul>

                <div x-show="!loading && open" class="border-t border-zinc-100 dark:border-zinc-800">
                    <a :href="'{{ route('dashboard') }}?search=' + encodeURIComponent(query)"
                       class="flex items-center justify-center gap-2 px-4 py-2.5 text-sm text-blue-600 hover:bg-zinc-50 dark:hover:bg-zinc-800 font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Show all results for "<span x-text="query"></span>"
                    </a>
                </div>
            </div>
        </template>
    </div>

    <script>
        function searchAutocomplete() {
            return {
                query: '{{ addslashes(request('search', '')) }}',
                suggestions: [],
                open: false,
                loading: false,
                dropX: 0, dropY: 0, dropW: 384,
                _abort: null,

                init() {
                    const close = () => this.close();
                    window.addEventListener('scroll', close, { passive: true });
                    window.addEventListener('resize', close, { passive: true });
                    document.addEventListener('click', (e) => {
                        if (!this.$refs.wrapper.contains(e.target)) this.close();
                    });
                },

                updatePosition() {
                    const rect = this.$refs.wrapper.getBoundingClientRect();
                    this.dropX = rect.left;
                    this.dropY = rect.bottom + 6;
                    this.dropW = Math.max(rect.width, 384);
                },

                async fetchSuggestions() {
                    const q = this.query.trim();
                    if (q.length < 2) { this.close(); return; }

                    this.updatePosition();
                    if (this._abort) this._abort.abort();
                    this._abort = new AbortController();
                    this.open = false;
                    this.loading = true;

                    try {
                        const res = await fetch('/api/books/suggest?q=' + encodeURIComponent(q), {
                            signal: this._abort.signal,
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (!res.ok) { this.close(); return; }
                        const data = await res.json();
                        this.suggestions = Array.isArray(data) ? data : [];
                        this.open = this.suggestions.length > 0;
                    } catch (e) {
                        if (e.name !== 'AbortError') this.close();
                    } finally {
                        this.loading = false;
                    }
                },

                selectBook(book) {
                    this.query = book.title;
                    this.close();
                    window.location.href = book.url;
                },

                submitForm() {
                    this.close();
                    window.location.href = '{{ route('dashboard') }}?search=' + encodeURIComponent(this.query);
                },

                close() { this.open = false; this.loading = false; }
            }
        }
    </script>

    {{-- Cart --}}
    <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
        <flux:tooltip content="Cart" position="bottom">
            <div class="relative">
                <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="shopping-cart" href="{{ route('cart.index') }}" />
                @php
                    $cartCount = auth()->check()
                        ? auth()->user()->cartItems()->count()
                        : array_sum(array_column(session('guest_cart', []), 'quantity'));
                @endphp
                @if ($cartCount > 0)
                    <span class="absolute top-0.5 right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-blue-600 text-white text-xs font-bold pointer-events-none">
                        {{ $cartCount > 9 ? '9+' : $cartCount }}
                    </span>
                @endif
            </div>
        </flux:tooltip>
    </flux:navbar>

    {{-- Favorites --}}
    @auth
        <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
            <flux:tooltip content="Favorites" position="bottom">
                <div class="relative">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="heart" href="{{ route('favorites.index') }}" />
                    @php
                        $favoriteCount = auth()->user()->favorites()->count();
                    @endphp
                    @if ($favoriteCount > 0)
                        <span class="absolute top-0.5 right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold pointer-events-none">
                            {{ $favoriteCount > 9 ? '9+' : $favoriteCount }}
                        </span>
                    @endif
                </div>
            </flux:tooltip>
        </flux:navbar>
    @endauth

    {{-- Profile / Login --}}
    @auth
        <x-desktop-user-menu />
    @else
        <flux:navbar class="py-0!">
            <flux:tooltip content="Sign in" position="bottom">
                <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="user" href="{{ route('login') }}" />
            </flux:tooltip>
        </flux:navbar>
    @endauth
</flux:header>

@if (request()->routeIs('books.*', 'authors.*', 'genres.*', 'orders.*', 'reviews.*'))
    <div class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 max-lg:hidden">
        <div class="flex items-center gap-1 px-4 py-2">
            @foreach ([
                'Authors'    => 'authors.index',
                'Books'      => 'books.index',
                'Genres'     => 'genres.index',
                'Orders'     => 'orders.index',
                'Reviews'    => 'reviews.index',
            ] as $label => $routeName)
                <a href="{{ route($routeName) }}"
                   class="text-sm px-3 py-1.5 rounded-lg transition-colors
                          {{ request()->routeIs(strtolower($label).'.*')
                             ? 'bg-zinc-100 dark:bg-zinc-800 font-medium text-zinc-900 dark:text-zinc-100'
                             : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>
@endif

<!-- Mobile Menu -->
<flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.header>
        <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
        <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        <flux:sidebar.group :heading="__('Platform')">
            <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:spacer />

    <flux:sidebar.nav>
        @auth
            <flux:sidebar.item icon="user" :href="route('profile.edit')" wire:navigate>
                {{ __('Profile') }}
            </flux:sidebar.item>
        @else
            <flux:sidebar.item icon="arrow-right-end-on-rectangle" :href="route('login')" wire:navigate>
                {{ __('Sign in') }}
            </flux:sidebar.item>
        @endauth
    </flux:sidebar.nav>

    {{-- Mobile search --}}
    <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
        <form method="GET" action="{{ route('dashboard') }}">
            <flux:input
                name="search"
                value="{{ request('search') }}"
                placeholder="Search books..."
                icon="magnifying-glass"
            />
        </form>
    </div>
</flux:sidebar>

{{ $slot }}

@fluxScripts
</body>
</html>
