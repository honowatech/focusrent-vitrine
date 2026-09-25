<x-admin.layout title="E-mail">
    <h1 class="mb-2 text-3xl font-bold text-white">Paramètres e-mail</h1>
    <p class="mb-6 max-w-3xl text-sm text-gray-400">Les messages du formulaire de contact restent dans le back-office et ne sont plus envoyés par e-mail. Cette page règle l’adresse de réception et le serveur SMTP, puis permet de vérifier l’envoi.</p>

    <form method="POST" action="{{ route('admin.mail.update') }}" class="grid max-w-3xl gap-4 rounded-xl bg-[#1D142B] p-6">
        @csrf
        <label class="grid gap-1 text-sm">
            E-mail de réception
            <input type="email" name="reception_email" value="{{ old('reception_email', $setting->reception_email) }}" required class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white">
        </label>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="grid gap-1 text-sm">
                Nom de l’expéditeur
                <input name="from_name" value="{{ old('from_name', $setting->from_name) }}" required class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white">
            </label>
            <label class="grid gap-1 text-sm">
                Adresse d’expédition
                <input type="email" name="from_address" value="{{ old('from_address', $setting->from_address) }}" required class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white">
            </label>
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            <label class="grid gap-1 text-sm md:col-span-2">
                Hôte SMTP
                <input name="host" value="{{ old('host', $setting->host) }}" required class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white">
            </label>
            <label class="grid gap-1 text-sm">
                Port
                <input type="number" name="port" min="1" max="65535" value="{{ old('port', $setting->port) }}" required class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white">
            </label>
        </div>
        <label class="grid gap-1 text-sm">
            Chiffrement
            @php $encryption = old('encryption', $setting->encryption); @endphp
            <select name="encryption" class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white">
                <option value="" @selected($encryption === null || $encryption === '')>Aucun</option>
                <option value="tls" @selected($encryption === 'tls')>TLS</option>
                <option value="ssl" @selected($encryption === 'ssl')>SSL</option>
            </select>
        </label>
        <label class="grid gap-1 text-sm">
            Nom d’utilisateur
            <input name="username" value="{{ old('username', $setting->username) }}" autocomplete="off" class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white">
        </label>
        <label class="grid gap-1 text-sm">
            Mot de passe
            <input type="password" name="password" autocomplete="new-password" class="min-h-12 rounded border border-gray-800 bg-black px-3 py-3 text-base text-white" placeholder="{{ $hasPassword ? 'Laisser vide pour conserver le mot de passe actuel' : '' }}">
        </label>
        @if ($errors->any())
            <ul class="text-sm text-red-500">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        <div class="flex flex-wrap gap-3">
            <button type="submit" name="intent" value="save" class="btn-gradient rounded-full px-5 py-2 font-bold">Enregistrer</button>
            <button type="submit" name="intent" value="test" class="rounded-full border border-white px-5 py-2 font-bold hover:bg-white hover:text-[#7D30FA]">Envoyer un e-mail de test</button>
        </div>
    </form>
</x-admin.layout>
