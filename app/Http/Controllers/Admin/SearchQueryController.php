<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SearchQuery;
use App\Support\Locales;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchQueryController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $period] = $this->range($request);

        $locale = (string) $request->query('locale', '');
        if (! Locales::isSupported($locale)) {
            $locale = '';
        }

        $source = (string) $request->query('source', '');
        if (! in_array($source, ['palette', 'page'], true)) {
            $source = '';
        }

        $base = fn (): Builder => SearchQuery::query()
            ->humans()
            ->where('searched_at', '>=', $from->copy()->utc())
            ->where('searched_at', '<', $to->copy()->addDay()->utc())
            ->when($locale !== '', fn ($q) => $q->where('locale', $locale))
            ->when($source !== '', fn ($q) => $q->where('source', $source));

        $totals = $base()
            ->selectRaw('count(*) as searches')
            ->selectRaw('count(distinct visitor_id) as visitors')
            ->selectRaw('sum(case when results_count = 0 then 1 else 0 end) as without_results')
            ->selectRaw('sum(case when clicked_id is not null then 1 else 0 end) as clicked')
            ->first();

        $searches = (int) ($totals->searches ?? 0);

        $top = $base()
            ->select('normalized')
            ->selectRaw('count(*) as searches')
            ->selectRaw('min(query) as sample')
            ->selectRaw('sum(case when results_count = 0 then 1 else 0 end) as without_results')
            ->selectRaw('sum(case when clicked_id is not null then 1 else 0 end) as clicked')
            ->selectRaw('min(locale) as locale')
            ->groupBy('normalized')
            ->orderByDesc('searches')
            ->orderBy('normalized')
            ->limit(30)
            ->get();

        $missing = $base()
            ->where('results_count', 0)
            ->select('normalized')
            ->selectRaw('count(*) as searches')
            ->selectRaw('min(query) as sample')
            ->selectRaw('min(locale) as locale')
            ->selectRaw('max(searched_at) as last_at')
            ->groupBy('normalized')
            ->orderByDesc('searches')
            ->orderBy('normalized')
            ->limit(30)
            ->get();

        $clicked = $base()
            ->whereNotNull('clicked_id')
            ->select('clicked_id', 'clicked_type')
            ->selectRaw('count(*) as clicks')
            ->selectRaw('avg(clicked_position) as avg_position')
            ->groupBy('clicked_id', 'clicked_type')
            ->orderByDesc('clicks')
            ->orderBy('clicked_id')
            ->limit(20)
            ->get();

        $byDay = $base()
            ->selectRaw('date(searched_at) as day, count(*) as searches')
            ->groupByRaw('date(searched_at)')
            ->pluck('searches', 'day');

        $series = [];
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $series[] = [
                'date' => $day->format('d/m'),
                'searches' => (int) ($byDay[$day->toDateString()] ?? 0),
            ];
        }
        $peak = max(1, ...array_column($series, 'searches'));

        return view('admin.searches', [
            'from' => $from,
            'to' => $to,
            'period' => $period,
            'locale' => $locale,
            'source' => $source,
            'searches' => $searches,
            'visitors' => (int) ($totals->visitors ?? 0),
            'withoutResults' => (int) ($totals->without_results ?? 0),
            'clicked' => (int) ($totals->clicked ?? 0),
            'noResultRate' => $searches > 0 ? round((int) ($totals->without_results ?? 0) / $searches * 100) : 0,
            'clickRate' => $searches > 0 ? round((int) ($totals->clicked ?? 0) / $searches * 100) : 0,
            'top' => $top,
            'missing' => $missing,
            'clickedResults' => $clicked,
            'series' => $series,
            'peak' => $peak,
        ]);
    }

    protected function range(Request $request): array
    {
        $period = (string) $request->query('period', '30');
        $today = now()->timezone('Africa/Douala')->startOfDay();

        if ($period === 'custom') {
            $from = $request->date('from')?->timezone('Africa/Douala')->startOfDay() ?? $today->copy()->subDays(29);
            $to = $request->date('to')?->timezone('Africa/Douala')->startOfDay() ?? $today->copy();
            if ($from->gt($to)) {
                [$from, $to] = [$to, $from];
            }

            return [$from, $to, 'custom'];
        }

        $days = match ($period) {
            'today' => 0,
            '7' => 6,
            '90' => 89,
            default => 29,
        };

        if (! in_array($period, ['today', '7', '90'], true)) {
            $period = '30';
        }

        return [$today->copy()->subDays($days), $today->copy(), $period];
    }
}
