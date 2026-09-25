<x-admin.layout title="Mode">
    <h1 class="mb-2 text-3xl font-bold text-white">Mode du site</h1>
    <p class="mb-6 max-w-3xl text-sm text-gray-400">Choisissez le mode de fonctionnement du site vitrine. Le back-office reste accessible quel que soit le mode actif.</p>

    <form method="POST" action="{{ route('admin.mode.update') }}" class="grid max-w-3xl gap-4 rounded-xl bg-[#1D142B] p-6">
        @csrf
        @php
            $descriptions = [
                'production' => 'Site accessible aux visiteurs, toutes les protections (reCAPTCHA) sont actives.',
                'maintenance' => 'Les visiteurs voient une page d’atterrissage indiquant que le site est en maintenance.',
                'development' => 'reCAPTCHA désactivé : les formulaires peuvent être testés sans perturber la production.',
            ];
            $current = old('mode', $setting->mode);
        @endphp
        @foreach (\App\Models\SiteSetting::MODES as $value => $label)
            <label class="flex cursor-pointer items-start gap-3 rounded-lg border {{ $current === $value ? 'border-[#7D30FA] bg-black/60' : 'border-gray-800 hover:border-gray-600' }} p-4">
                <input type="radio" name="mode" value="{{ $value }}" @checked($current === $value) class="mt-1 accent-[#7D30FA]">
                <span class="grid gap-1">
                    <span class="font-semibold text-white">{{ $label }}</span>
                    <span class="text-sm text-gray-400">{{ $descriptions[$value] }}</span>
                </span>
            </label>
        @endforeach
        @if ($errors->any())
            <ul class="text-sm text-red-500">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        <div>
            <button type="submit" class="btn-gradient rounded-full px-5 py-2 font-bold">Enregistrer</button>
        </div>
    </form>
</x-admin.layout>
