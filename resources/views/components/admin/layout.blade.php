@props(['title' => 'Back-office'])
@php
    $unreadMessages = \App\Models\ContactMessage::query()->whereNull('read_at')->count();
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} — Focus Rent</title>
    @include('admin.partials.pwa')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0A0413] text-gray-200">
    <header class="sticky top-0 z-20 border-b border-gray-800 bg-black/90">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-4">
            <a href="{{ route('admin.visits') }}" class="text-xl font-bold gradient-text">Focus Rent</a>
            <div class="flex items-center gap-3">
                <button type="button" data-pwa-install hidden class="rounded-full border border-[#FC912C] px-3 py-1 text-xs font-semibold text-[#FC912C]">Installer</button>
                <form method="POST" action="{{ route('admin.logout') }}" class="md:hidden">
                    @csrf
                    <button type="submit" class="text-sm font-semibold hover:text-white">Sortir</button>
                </form>
            </div>
            <nav class="hidden flex-wrap items-center gap-4 text-sm font-semibold md:flex" aria-label="Back-office">
                <a href="{{ route('admin.visits') }}" class="{{ request()->routeIs('admin.visits') ? 'text-[#FC912C]' : 'hover:text-white' }}">Visites</a>
                <a href="{{ route('admin.messages') }}" class="{{ request()->routeIs('admin.messages', 'admin.messages.show') ? 'text-[#FC912C]' : 'hover:text-white' }}">
                    Messages
                    @if ($unreadMessages > 0)
                        <span class="ml-1 rounded-full bg-[#7D30FA] px-2 py-0.5 text-xs text-white">{{ $unreadMessages }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.mail') }}" class="{{ request()->routeIs('admin.mail') ? 'text-[#FC912C]' : 'hover:text-white' }}">E-mail</a>
                <a href="{{ route('admin.mode') }}" class="{{ request()->routeIs('admin.mode') ? 'text-[#FC912C]' : 'hover:text-white' }}">Mode</a>
                <a href="{{ url('/') }}" class="hover:text-white" target="_blank" rel="noopener noreferrer">Voir le site</a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="hover:text-white">Déconnexion</button>
                </form>
            </nav>
        </div>
    </header>
    <main id="main-content" class="mx-auto max-w-6xl px-4 py-8 pb-24 md:pb-8">
        @if (session('success'))
            <p class="mb-6 rounded-lg border border-green-400/40 bg-green-400/10 px-4 py-3 text-green-400" role="status">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="mb-6 rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-red-500" role="alert">{{ session('error') }}</p>
        @endif
        {{ $slot }}
    </main>
    <nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t border-gray-800 bg-black md:hidden" style="padding-bottom: env(safe-area-inset-bottom)" aria-label="Application">
        <a href="{{ route('admin.visits') }}" class="px-2 py-4 text-center text-sm font-semibold {{ request()->routeIs('admin.visits') ? 'text-[#FC912C]' : 'text-gray-300' }}">Visites</a>
        <a href="{{ route('admin.messages') }}" class="px-2 py-4 text-center text-sm font-semibold {{ request()->routeIs('admin.messages', 'admin.messages.show') ? 'text-[#FC912C]' : 'text-gray-300' }}">
            Messages
            @if ($unreadMessages > 0)
                <span>{{ $unreadMessages }}</span>
            @endif
        </a>
        <a href="{{ route('admin.mail') }}" class="px-2 py-4 text-center text-sm font-semibold {{ request()->routeIs('admin.mail') ? 'text-[#FC912C]' : 'text-gray-300' }}">E-mail</a>
        <a href="{{ route('admin.mode') }}" class="px-2 py-4 text-center text-sm font-semibold {{ request()->routeIs('admin.mode') ? 'text-[#FC912C]' : 'text-gray-300' }}">Mode</a>
    </nav>
</body>
</html>
