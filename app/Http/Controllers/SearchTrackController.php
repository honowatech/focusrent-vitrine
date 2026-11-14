<?php

namespace App\Http\Controllers;

use App\Support\Locales;
use App\Support\SearchIndex;
use App\Support\SearchTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchTrackController extends Controller
{
    /**
     * Beacon de la palette : corps JSON envoyé en text/plain, sans CSRF.
     */
    public function store(Request $request): Response
    {
        $raw = (string) $request->getContent();
        $data = json_decode($raw, true);

        if (! is_array($data) || strlen($raw) > 2048) {
            return response('', 400);
        }

        $query = isset($data['query']) && is_string($data['query']) ? trim($data['query']) : '';
        $locale = isset($data['locale']) && is_string($data['locale']) ? $data['locale'] : '';
        $min = (int) config('search.min_chars', 2);
        $max = (int) config('search.max_chars', 80);

        if ($query === '' || mb_strlen($query) < $min || mb_strlen($query) > $max || ! Locales::isSupported($locale)) {
            return response('', 422);
        }

        SearchTracker::record($request, [
            'query' => $query,
            'locale' => $locale,
            'source' => 'palette',
            'results_count' => is_numeric($data['results_count'] ?? null) ? (int) $data['results_count'] : 0,
            'clicked_id' => isset($data['clicked_id']) && is_string($data['clicked_id']) ? $data['clicked_id'] : null,
            'clicked_type' => isset($data['clicked_type']) && is_string($data['clicked_type']) ? $data['clicked_type'] : null,
            'clicked_position' => is_numeric($data['clicked_position'] ?? null) ? (int) $data['clicked_position'] : null,
        ]);

        return response('', 204);
    }

    /**
     * Clic depuis la page de résultats : complète la ligne du jeton puis
     * redirige vers le document (URL prise dans l'index, jamais dans la requête).
     */
    public function go(Request $request): RedirectResponse
    {
        $locale = (string) $request->query('l', app()->getLocale());
        if (! Locales::isSupported($locale)) {
            $locale = Locales::default();
        }

        $id = (string) $request->query('id', '');
        $document = null;

        foreach (SearchIndex::load($locale)['documents'] as $candidate) {
            if ($candidate['id'] === $id) {
                $document = $candidate;
                break;
            }
        }

        if ($document === null) {
            return redirect(Locales::route('search', [], $locale));
        }

        SearchTracker::click(
            (string) $request->query('t', ''),
            $document['id'],
            $document['type'],
            (int) $request->query('p', 1)
        );

        return redirect($document['url']);
    }
}
