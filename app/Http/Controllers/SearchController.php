<?php

namespace App\Http\Controllers;

use App\Support\SearchEngine;
use App\Support\SearchTracker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchController extends Controller
{
    public function show(Request $request): Response
    {
        $rules = (array) config('search');
        $locale = app()->getLocale();
        $query = trim((string) $request->query('q', ''));
        $query = mb_substr(preg_replace('/\s+/u', ' ', $query) ?? '', 0, (int) $rules['max_chars']);
        $type = (string) $request->query('type', '');
        if (! in_array($type, (array) $rules['groups'], true)) {
            $type = '';
        }

        $engine = SearchEngine::for($locale);
        $tooShort = $query !== '' && mb_strlen($query) < (int) $rules['min_chars'];
        $groups = ($query === '' || $tooShort) ? [] : $engine->grouped($query);
        $terms = $engine->highlightTerms($query);
        $counts = array_map('count', $groups);
        $total = array_sum($counts);
        $perGroup = (int) $rules['per_group_page'];

        // Une entrée par requête : ni pour les filtres ni pour le retour, qui gardent le jeton.
        $token = (string) $request->query('t', '');
        if ($query !== '' && ! $tooShort && ! preg_match('/^[a-z0-9]{32}$/', $token)) {
            $token = SearchTracker::record($request, [
                'query' => $query,
                'locale' => $locale,
                'source' => 'page',
                'results_count' => $total,
            ]);
        }

        $visible = $groups;
        if ($type !== '') {
            $visible = array_intersect_key($groups, [$type => true]);
        } else {
            $visible = array_map(fn (array $results) => array_slice($results, 0, $perGroup), $groups);
        }

        $html = view('pages.search', [
            'query' => $query,
            'type' => $type,
            'tooShort' => $tooShort,
            'groups' => $visible,
            'counts' => $counts,
            'total' => $total,
            'terms' => $terms,
            'token' => $token,
            'perGroup' => $perGroup,
            'rules' => $rules,
        ])->render();

        return response($html, 200)
            ->header('X-Robots-Tag', 'noindex, follow')
            ->header('Cache-Control', 'no-store, private');
    }
}
