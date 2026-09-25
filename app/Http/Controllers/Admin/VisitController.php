<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $period] = $this->range($request);

        $totals = DB::table('page_views')
            ->whereDate('viewed_on', '>=', $from->toDateString())
            ->whereDate('viewed_on', '<=', $to->toDateString())
            ->selectRaw('count(*) as views, count(distinct visitor) as visitors')
            ->first();

        $pages = DB::table('page_views')
            ->whereDate('viewed_on', '>=', $from->toDateString())
            ->whereDate('viewed_on', '<=', $to->toDateString())
            ->select('path', 'page')
            ->selectRaw('count(*) as views, count(distinct visitor) as visitors')
            ->groupBy('path', 'page')
            ->orderByDesc('views')
            ->get()
            ->map(function ($row) {
                $row->label = ($row->page && \Illuminate\Support\Facades\Lang::has('seo.'.$row->page.'.nav'))
                    ? __('seo.'.$row->page.'.nav')
                    : $row->path;

                return $row;
            });

        $byDay = DB::table('page_views')
            ->whereDate('viewed_on', '>=', $from->toDateString())
            ->whereDate('viewed_on', '<=', $to->toDateString())
            ->selectRaw('date(viewed_on) as viewed_on, count(*) as views')
            ->groupByRaw('date(viewed_on)')
            ->pluck('views', 'viewed_on');

        $series = [];
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $key = $day->toDateString();
            $series[] = [
                'date' => $day->format('d/m'),
                'views' => (int) ($byDay[$key] ?? 0),
            ];
        }

        $peak = max(1, ...array_column($series, 'views'));

        return view('admin.visits', [
            'from' => $from,
            'to' => $to,
            'period' => $period,
            'views' => (int) ($totals->views ?? 0),
            'visitors' => (int) ($totals->visitors ?? 0),
            'pages' => $pages,
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
