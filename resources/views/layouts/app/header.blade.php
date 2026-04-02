<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
<flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

    <flux:navbar class="-mb-px max-lg:hidden">
        <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
            {{ __('Dashboard') }}
        </flux:navbar.item>
    </flux:navbar>

    <flux:spacer />

    {{-- Search --}}
    <form method="GET" action="{{ route('dashboard') }}" class="max-lg:hidden">
        <flux:input
            name="search"
            value="{{ request('search') }}"
            placeholder="Search books..."
            icon="magnifying-glass"
            class="w-64"
        />
    </form>

    {{-- Cart --}}
    <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
        <flux:tooltip content="Cart" position="bottom">
            <div class="relative">
                <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="shopping-cart" href="{{ route('cart.index') }}" />
                @auth
                    @php $cartCount = auth()->user()->cartItems()->count(); @endphp
                    @if ($cartCount > 0)
                        <span class="absolute top-0.5 right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-blue-600 text-white text-xs font-bold pointer-events-none">
                                    {{ $cartCount > 9 ? '9+' : $cartCount }}
                                </span>
                    @endif
                @endauth
            </div>
        </flux:tooltip>
    </flux:navbar>

    <x-desktop-user-menu />
</flux:header>

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
        <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
            {{ __('Repository') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
            {{ __('Documentation') }}
        </flux:sidebar.item>
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
