<x-admin.layout title="Visites">
    <h1 class="mb-2 text-3xl font-bold text-white">Visites</h1>
    <p class="mb-6 text-sm text-gray-400">Du {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}, heure de Douala. Les robots connus ne sont pas comptés.</p>

    <div class="mb-8 grid gap-3">
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
            @foreach (['today' => 'Aujourd’hui', '7' => '7 jours', '30' => '30 jours', '90' => '90 jours'] as $key => $label)
                <a href="{{ route('admin.visits', ['period' => $key]) }}" class="rounded-full px-4 py-3 text-center text-sm font-semibold sm:w-auto {{ $period === $key ? 'btn-gradient' : 'border border-gray-800 hover:text-white' }}">{{ $label }}</a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.visits') }}" class="grid grid-cols-2 gap-2 sm:flex sm:items-end">
            <input type="hidden" name="period" value="custom">
            <label class="grid text-xs text-gray-400">Du
                <input type="date" name="from" value="{{ $from->toDateString() }}" required class="min-h-12 rounded border border-gray-800 bg-black px-2 py-2 text-base text-white">
            </label>
            <label class="grid text-xs text-gray-400">Au
                <input type="date" name="to" value="{{ $to->toDateString() }}" required class="min-h-12 rounded border border-gray-800 bg-black px-2 py-2 text-base text-white">
            </label>
            <button type="submit" class="col-span-2 min-h-12 rounded-full border border-gray-800 px-4 py-3 text-sm font-semibold hover:text-white sm:col-span-1">Appliquer</button>
        </form>
    </div>

    <div class="mb-8 grid gap-4 md:grid-cols-2">
        <section class="rounded-xl bg-[#1D142B] p-6">
            <p class="text-sm text-gray-400">Pages vues</p>
            <p class="text-4xl font-bold text-white">{{ number_format($views, 0, ',', ' ') }}</p>
        </section>
        <section class="rounded-xl bg-[#1D142B] p-6">
            <p class="text-sm text-gray-400">Visiteurs (sessions distinctes)</p>
            <p class="text-4xl font-bold text-white">{{ number_format($visitors, 0, ',', ' ') }}</p>
        </section>
    </div>

    <section class="mb-8 rounded-xl bg-[#130822] p-6">
        <h2 class="mb-4 text-lg font-semibold text-white">Par jour</h2>
        <div class="grid gap-2">
            @foreach ($series as $row)
                <div class="grid grid-cols-[4.5rem_1fr_3rem] items-center gap-3 text-sm">
                    <span class="text-gray-400">{{ $row['date'] }}</span>
                    <span class="h-2 overflow-hidden rounded-full bg-gray-800">
                        <span class="block h-2 rounded-full bg-[#7D30FA]" style="width: {{ (int) round($row['views'] / $peak * 100) }}%"></span>
                    </span>
                    <span class="text-right">{{ $row['views'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="overflow-x-auto rounded-xl bg-[#1D142B]">
        <table class="w-full min-w-[36rem] text-left text-sm">
            <caption class="sr-only">Visites par page</caption>
            <thead>
                <tr class="border-b border-gray-800 text-gray-400">
                    <th class="px-4 py-3 font-semibold" scope="col">Page</th>
                    <th class="px-4 py-3 font-semibold" scope="col">Chemin</th>
                    <th class="px-4 py-3 font-semibold" scope="col">Vues</th>
                    <th class="px-4 py-3 font-semibold" scope="col">Visiteurs</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pages as $row)
                    <tr class="border-b border-gray-800">
                        <th class="px-4 py-3 font-semibold text-white" scope="row">{{ $row->label }}</th>
                        <td class="px-4 py-3">{{ $row->path }}</td>
                        <td class="px-4 py-3">{{ $row->views }}</td>
                        <td class="px-4 py-3">{{ $row->visitors }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-gray-400" colspan="4">Aucune visite sur cette période.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-admin.layout>
