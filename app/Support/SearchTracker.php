<?php

namespace App\Support;

use App\Models\SearchQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Écrit le journal des recherches sans jamais casser la recherche :
 * tout passe par un try/catch et s'exécute après l'envoi de la réponse.
 */
class SearchTracker
{
    /**
     * Enregistre une recherche et retourne son jeton (pour rattacher un clic).
     *
     * @param  array{query: string, locale: string, source: string, results_count?: int, clicked_id?: ?string, clicked_type?: ?string, clicked_position?: ?int}  $data
     */
    public static function record(Request $request, array $data): string
    {
        $token = Str::lower(Str::random(32));
        $query = Str::limit(trim(preg_replace('/\s+/u', ' ', (string) $data['query']) ?? ''), 80, '');

        if ($query === '') {
            return $token;
        }

        $attributes = [
            'query' => $query,
            'normalized' => Str::limit(implode(' ', SearchText::tokens($query, false)), 80, ''),
            'locale' => Str::limit((string) $data['locale'], 8, ''),
            'source' => in_array($data['source'], ['palette', 'page'], true) ? $data['source'] : 'page',
            'token' => $token,
            'results_count' => max(0, (int) ($data['results_count'] ?? 0)),
            'clicked_id' => self::clickedId($data['clicked_id'] ?? null),
            'clicked_type' => isset($data['clicked_type']) ? Str::limit((string) $data['clicked_type'], 32, '') : null,
            'clicked_position' => isset($data['clicked_position']) ? max(1, min(999, (int) $data['clicked_position'])) : null,
            'visitor_id' => Visitor::id($request),
            'is_bot' => Visitor::isBot($request->userAgent()),
            'searched_at' => now(),
        ];

        self::defer(function () use ($attributes) {
            if (Schema::hasTable('search_queries')) {
                SearchQuery::create($attributes);
            }
        });

        return $token;
    }

    /**
     * Complète la ligne d'un jeton avec le résultat choisi, une seule fois, dans l'heure.
     */
    public static function click(string $token, string $id, ?string $type, int $position): void
    {
        if (! preg_match('/^[a-z0-9]{32}$/', $token)) {
            return;
        }

        $clickedId = self::clickedId($id);
        $type = $type !== null ? Str::limit($type, 32, '') : null;
        $position = max(1, min(999, $position));

        self::defer(function () use ($token, $clickedId, $type, $position) {
            if (! Schema::hasTable('search_queries')) {
                return;
            }

            SearchQuery::query()
                ->where('token', $token)
                ->whereNull('clicked_id')
                ->where('searched_at', '>=', now()->subHour())
                ->update([
                    'clicked_id' => $clickedId,
                    'clicked_type' => $type,
                    'clicked_position' => $position,
                ]);
        });
    }

    protected static function clickedId(?string $id): ?string
    {
        if ($id === null || $id === '') {
            return null;
        }

        return Str::limit(preg_replace('/[^\w:#.-]/u', '', $id) ?? '', 120, '');
    }

    protected static function defer(\Closure $work): void
    {
        $safe = function () use ($work) {
            try {
                $work();
            } catch (\Throwable $e) {
                report($e);
            }
        };

        if (app()->runningUnitTests() || app()->runningInConsole()) {
            $safe();

            return;
        }

        app()->terminating($safe);
    }
}
