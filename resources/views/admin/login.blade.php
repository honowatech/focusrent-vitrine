<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $needsSetup ? 'Créer le compte administrateur' : 'Connexion' }} — Focus Rent</title>
    @include('admin.partials.pwa')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0A0413] text-gray-200" style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom)">
    <main class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10">
        <h1 class="mb-2 text-center text-3xl font-bold gradient-text">
            {{ $needsSetup ? 'Créer le compte administrateur' : 'Connexion' }}
        </h1>
        <p class="mb-6 text-center text-sm text-gray-400">
            {{ $needsSetup ? 'Aucun compte n’existe encore. Ce formulaire ne s’affiche qu’une fois.' : 'Back-office Focus Rent' }}
        </p>
        @if (session('error'))
            <p class="mb-4 rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-500" role="alert">{{ session('error') }}</p>
        @endif
        <form method="POST" action="{{ $needsSetup ? url('/admin/setup') : url('/admin/login') }}" class="grid gap-4 rounded-xl bg-[#1D142B] p-5 sm:p-8" autocomplete="on">
            @csrf
            @if ($needsSetup)
                <label class="grid gap-1 text-sm">
                    Nom
                    <input name="name" value="{{ old('name') }}" required autocomplete="name" class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white">
                </label>
            @endif
            <label class="grid gap-1 text-sm">
                E-mail
                <input type="email" name="email" value="{{ old('email', $needsSetup ? '' : config('admin.email')) }}" required autofocus autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false" class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white">
            </label>
            <label class="grid gap-1 text-sm">
                Mot de passe
                <input type="password" name="password" required autocomplete="{{ $needsSetup ? 'new-password' : 'current-password' }}" class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white">
            </label>
            @if ($needsSetup)
                <label class="grid gap-1 text-sm">
                    Confirmer le mot de passe
                    <input type="password" name="password_confirmation" required autocomplete="new-password" class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white">
                </label>
            @else
                <label class="flex min-h-12 items-center gap-3 text-base">
                    <input type="checkbox" name="remember" value="1" class="h-5 w-5">
                    Rester connecté
                </label>
            @endif
            @if ($errors->any())
                <ul class="text-sm text-red-500">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <button type="submit" class="btn-gradient min-h-12 rounded-full py-3 text-base font-bold">
                {{ $needsSetup ? 'Créer et entrer' : 'Entrer' }}
            </button>
            <button type="button" data-pwa-install hidden class="min-h-12 rounded-full border border-[#FC912C] py-3 text-base font-semibold text-[#FC912C]">Installer sur le téléphone</button>
        </form>
    </main>
</body>
</html>
