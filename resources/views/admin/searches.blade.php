@php
    use App\Support\Locales;

    $filters = fn (array $overrides = []) => route('admin.searches', array_filter(array_merge([
        'period' => $period,
        'from' => $period === 'custom' ? $from->toDateString() : null,
        'to' => $period === 'custom' ? $to->toDateString() : null,
        'locale' => $locale,
        'source' => $source,
    ], $overrides), fn ($value) => $value !== null && $value !== ''));

    $testUrl = fn ($row) => Locales::route('search', ['q' => $row->sample], Locales::isSupported((string) $row->locale) ? $row->locale : Locales::default());
    $labelFor = fn (?string $id) => $id ? (\Illuminate\Support\Str::of($id)->after(':')->toString()) : '—';
@endphp
<x-admin.layout title="Recherches">
    <h1 class="mb-2 text-3xl font-bold text-white">Recherches</h1>
    <p class="mb-6 text-sm text-gray-400">Du {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}, heure de Douala. Les robots connus ne sont pas comptés, aucune adresse IP n’est conservée.</p>

    <div class="mb-8 grid gap-3">
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
            @foreach (['today' => 'Aujourd’hui', '7' => '7 jours', '30' => '30 jours', '90' => '90 jours'] as $key => $label)
                <a href="{{ $filters(['period' => $key, 'from' => null, 'to' => null]) }}" class="rounded-full px-4 py-3 text-center text-sm font-semibold sm:w-auto {{ $period === $key ? 'btn-gradient' : 'border border-gray-800 hover:text-white' }}">{{ $label }}</a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.searches') }}" class="grid grid-cols-2 gap-2 sm:flex sm:items-end">
            <input type="hidden" name="period" value="custom">
            <label class="grid text-xs text-gray-400">Du
                <input type="date" name="from" value="{{ $from->toDateString() }}" required class="min-h-12 rounded border border-gray-800 bg-black px-2 py-2 text-base text-white">
            </label>
            <label class="grid text-xs text-gray-400">Au
                <input type="date" name="to" value="{{ $to->toDateString() }}" required class="min-h-12 rounded border border-gray-800 bg-black px-2 py-2 text-base text-white">
            </label>
            <label class="grid text-xs text-gray-400">Langue
                <select name="locale" class="min-h-12 rounded border border-gray-800 bg-black px-2 py-2 text-base text-white">
                    <option value="">Toutes</option>
                    @foreach (Locales::available() as $code => $meta)
                        <option value="{{ $code }}" @selected($locale === $code)>{{ $meta['native'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid text-xs text-gray-400">Origine
                <select name="source" class="min-h-12 rounded border border-gray-800 bg-black px-2 py-2 text-base text-white">
                    <option value="">Toutes</option>
                    <option value="palette" @selected($source === 'palette')>Palette (Ctrl K)</option>
                    <option value="page" @selected($source === 'page')>Page de résultats</option>
                </select>
            </label>
            <button type="submit" class="col-span-2 min-h-12 rounded-full border border-gray-800 px-4 py-3 text-sm font-semibold hover:text-white sm:col-span-1">Appliquer</button>
        </form>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <section class="rounded-xl bg-[#1D142B] p-6">
            <p class="text-sm text-gray-400">Recherches</p>
            <p class="text-4xl font-bold text-white">{{ number_format($searches, 0, ',', ' ') }}</p>
        </section>
        <section class="rounded-xl bg-[#1D142B] p-6">
            <p class="text-sm text-gray-400">Visiteurs</p>
            <p class="text-4xl font-bold text-white">{{ number_format($visitors, 0, ',', ' ') }}</p>
        </section>
        <section class="rounded-xl bg-[#1D142B] p-6">
            <p class="text-sm text-gray-400">Sans résultat</p>
            <p class="text-4xl font-bold text-white">{{ $noResultRate }} %</p>
            <p class="text-xs text-gray-400">{{ number_format($withoutResults, 0, ',', ' ') }} recherches</p>
        </section>
        <section class="rounded-xl bg-[#1D142B] p-6">
            <p class="text-sm text-gray-400">Résultat choisi</p>
            <p class="text-4xl font-bold text-white">{{ $clickRate }} %</p>
            <p class="text-xs text-gray-400">{{ number_format($clicked, 0, ',', ' ') }} clics</p>
        </section>
    </div>

    <section class="mb-8 rounded-xl bg-[#130822] p-6">
        <h2 class="mb-4 text-lg font-semibold text-white">Par jour</h2>
        <div class="grid gap-2">
            @foreach ($series as $row)
                <div class="grid grid-cols-[4.5rem_1fr_3rem] items-center gap-3 text-sm">
                    <span class="text-gray-400">{{ $row['date'] }}</span>
                    <span class="h-2 overflow-hidden rounded-full bg-gray-800">
                        <span class="block h-2 rounded-full bg-[#7D30FA]" style="width: {{ (int) round($row['searches'] / $peak * 100) }}%"></span>
                    </span>
                    <span class="text-right">{{ $row['searches'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-8 lg:grid-cols-2">
        <section class="overflow-x-auto rounded-xl bg-[#1D142B]">
            <h2 class="px-4 pt-5 text-lg font-semibold text-white">Requêtes les plus fréquentes</h2>
            <table class="w-full min-w-[28rem] text-left text-sm">
                <caption class="sr-only">Requêtes les plus fréquentes</caption>
                <thead>
                    <tr class="border-b border-gray-800 text-gray-400">
                        <th class="px-4 py-3 font-semibold" scope="col">Requête</th>
                        <th class="px-4 py-3 font-semibold" scope="col">Fois</th>
                        <th class="px-4 py-3 font-semibold" scope="col">Sans résultat</th>
                        <th class="px-4 py-3 font-semibold" scope="col">Clics</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($top as $row)
                        <tr class="border-b border-gray-800">
                            <th class="px-4 py-3 font-semibold text-white" scope="row">
                                <a href="{{ $testUrl($row) }}" target="_blank" rel="noopener noreferrer" class="hover:text-[#FC912C]" title="Tester sur le site">{{ $row->sample }}</a>
                                <span class="ml-1 text-xs uppercase text-gray-500">{{ $row->locale }}</span>
                            </th>
                            <td class="px-4 py-3">{{ $row->searches }}</td>
                            <td class="px-4 py-3">{{ $row->without_results }}</td>
                            <td class="px-4 py-3">{{ $row->clicked }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-gray-400" colspan="4">Aucune recherche sur cette période.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="overflow-x-auto rounded-xl bg-[#1D142B]">
            <h2 class="px-4 pt-5 text-lg font-semibold text-white">Requêtes sans résultat</h2>
            <p class="px-4 pt-1 text-xs text-gray-400">La liste des contenus à écrire ou des synonymes à ajouter.</p>
            <table class="w-full min-w-[24rem] text-left text-sm">
                <caption class="sr-only">Requêtes sans résultat</caption>
                <thead>
                    <tr class="border-b border-gray-800 text-gray-400">
                        <th class="px-4 py-3 font-semibold" scope="col">Requête</th>
                        <th class="px-4 py-3 font-semibold" scope="col">Fois</th>
                        <th class="px-4 py-3 font-semibold" scope="col">Dernière</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($missing as $row)
                        <tr class="border-b border-gray-800">
                            <th class="px-4 py-3 font-semibold text-white" scope="row">
                                <a href="{{ $testUrl($row) }}" target="_blank" rel="noopener noreferrer" class="hover:text-[#FC912C]" title="Tester sur le site">{{ $row->sample }}</a>
                                <span class="ml-1 text-xs uppercase text-gray-500">{{ $row->locale }}</span>
                            </th>
                            <td class="px-4 py-3">{{ $row->searches }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($row->last_at)->timezone('Africa/Douala')->format('d/m H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-gray-400" colspan="3">Toutes les recherches ont trouvé quelque chose.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>

    <section class="mt-8 overflow-x-auto rounded-xl bg-[#1D142B]">
        <h2 class="px-4 pt-5 text-lg font-semibold text-white">Résultats les plus choisis</h2>
        <table class="w-full min-w-[28rem] text-left text-sm">
            <caption class="sr-only">Résultats les plus choisis</caption>
            <thead>
                <tr class="border-b border-gray-800 text-gray-400">
                    <th class="px-4 py-3 font-semibold" scope="col">Résultat</th>
                    <th class="px-4 py-3 font-semibold" scope="col">Type</th>
                    <th class="px-4 py-3 font-semibold" scope="col">Clics</th>
                    <th class="px-4 py-3 font-semibold" scope="col">Position moyenne</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clickedResults as $row)
                    <tr class="border-b border-gray-800">
                        <th class="px-4 py-3 font-semibold text-white" scope="row">{{ $labelFor($row->clicked_id) }}</th>
                        <td class="px-4 py-3">{{ $row->clicked_type ? __('search.groups.'.$row->clicked_type) : '—' }}</td>
                        <td class="px-4 py-3">{{ $row->clicks }}</td>
                        <td class="px-4 py-3">{{ $row->avg_position ? number_format((float) $row->avg_position, 1, ',', ' ') : '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-gray-400" colspan="4">Aucun résultat choisi sur cette période.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-admin.layout>
