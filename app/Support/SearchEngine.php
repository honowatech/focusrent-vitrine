<?php

namespace App\Support;

/**
 * Moteur de la page de résultats serveur. Il lit le même index que la
 * palette et reproduit le calcul de MiniSearch (BM25, poids préfixe et
 * faute, synonymes pondérés, mot connu) avec les règles de config/search.php,
 * pour que la page et la palette classent les mêmes résultats dans le même ordre.
 */
class SearchEngine
{
    public const FIELDS = ['label', 'title', 'excerpt', 'domain', 'body'];

    protected const BM25_K = 1.2;

    protected const BM25_B = 0.7;

    protected const BM25_D = 0.5;

    protected const PREFIX_WEIGHT = 0.375;

    protected const FUZZY_WEIGHT = 0.45;

    protected const SYNONYM_WEIGHT = 0.7;

    /** @var list<array<string, mixed>> */
    protected array $documents;

    /** @var array<string, array<string, array<int, int>>> champ => mot => [doc => tf] */
    protected array $postings = [];

    /** @var array<int, array<string, int>> doc => champ => longueur (mots uniques) */
    protected array $lengths = [];

    /** @var array<string, float> */
    protected array $averages = [];

    /** @var array<string, true> */
    protected array $known = [];

    protected array $rules;

    public function __construct(string $locale, ?array $index = null)
    {
        $this->rules = (array) config('search');
        $this->documents = array_values(($index ?? SearchIndex::load($locale))['documents']);
        $this->analyse();
    }

    public static function for(string $locale): self
    {
        return new self($locale);
    }

    /**
     * @return list<string>
     */
    public function queryTerms(string $query): array
    {
        return array_values(array_unique(array_slice(SearchText::tokens($query), 0, 12)));
    }

    /**
     * Résultats classés comme dans la palette : score décroissant, puis les
     * prolongements des mots connus, loin derrière.
     *
     * @return list<array{document: array<string, mixed>, score: float, terms: list<string>}>
     */
    public function search(string $query): array
    {
        $query = trim($query);
        if (mb_strlen($query) < (int) ($this->rules['min_chars'] ?? 2)) {
            return [];
        }

        $terms = $this->queryTerms($query);
        if ($terms === []) {
            return [];
        }

        $results = $this->combine($terms, 'AND', false);
        if ($results === [] && count($terms) > 1) {
            $results = $this->combine($terms, 'OR', false);
        }

        $ordered = $this->sorted($results);

        if ($this->hasKnown($terms)) {
            $extra = $this->combine($terms, $results === [] ? 'OR' : 'AND', true);
            $extra = array_diff_key($extra, $results);
            $weight = (float) ($this->rules['known_word']['prefix_weight'] ?? 0.25);
            foreach ($extra as &$hit) {
                $hit['score'] *= $weight;
            }
            unset($hit);
            $ordered = array_merge($ordered, $this->sorted($extra));
        }

        return $ordered;
    }

    /**
     * Résultats groupés par type dans l'ordre de config('search.groups').
     *
     * @return array<string, list<array{document: array<string, mixed>, score: float, terms: list<string>}>>
     */
    public function grouped(string $query): array
    {
        $groups = array_fill_keys((array) ($this->rules['groups'] ?? []), []);

        foreach ($this->search($query) as $result) {
            $type = (string) $result['document']['type'];
            $groups[$type] ??= [];
            $groups[$type][] = $result;
        }

        return array_filter($groups);
    }

    /**
     * Mots à surligner : ceux de la requête et leurs synonymes.
     *
     * @return list<string>
     */
    public function highlightTerms(string $query): array
    {
        $all = [];

        foreach ($this->queryTerms($query) as $term) {
            $all[] = $term;
            foreach ($this->synonymsOf($term) as $synonym) {
                $all[] = $synonym;
            }
        }

        return array_values(array_unique(array_filter($all, fn ($t) => mb_strlen($t) >= 2)));
    }

    /**
     * Pose des <mark> insensibles aux accents sur un texte échappé.
     *
     * @param  list<string>  $terms
     */
    public static function highlight(string $text, array $terms): string
    {
        $terms = array_values(array_filter($terms, fn ($t) => $t !== ''));
        if ($terms === []) {
            return e($text);
        }

        $folded = SearchText::fold($text);
        if (mb_strlen($folded) !== mb_strlen($text)) {
            return e($text);
        }

        usort($terms, fn ($a, $b) => strlen($b) <=> strlen($a));
        $pattern = '/(?<![a-z0-9])('.implode('|', array_map(fn ($t) => preg_quote($t, '/'), $terms)).')/iu';
        $ranges = [];

        if (preg_match_all($pattern, $folded, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as [$match, $byteOffset]) {
                $start = mb_strlen(substr($folded, 0, $byteOffset));
                $ranges[] = [$start, $start + mb_strlen($match)];
            }
        }

        if ($ranges === []) {
            return e($text);
        }

        $out = '';
        $cursor = 0;
        foreach ($ranges as [$start, $end]) {
            if ($start < $cursor) {
                continue;
            }
            $out .= e(mb_substr($text, $cursor, $start - $cursor));
            $out .= '<mark>'.e(mb_substr($text, $start, $end - $start)).'</mark>';
            $cursor = $end;
        }

        return $out.e(mb_substr($text, $cursor));
    }

    /**
     * Recentre l'extrait autour du premier mot trouvé.
     *
     * @param  list<string>  $terms
     */
    public static function snippet(string $text, array $terms, int $length = 160): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $folded = SearchText::fold($text);
        $position = null;

        foreach ($terms as $term) {
            $found = mb_strpos($folded, $term);
            if ($found !== false && ($position === null || $found < $position)) {
                $position = $found;
            }
        }

        if ($position === null || $position < $length * 0.6) {
            return SearchText::limit($text, $length);
        }

        $start = max(0, $position - (int) ($length * 0.3));
        $space = mb_strpos($text, ' ', $start);
        if ($space !== false && $space - $start < 20) {
            $start = $space + 1;
        }

        return '…'.SearchText::limit(mb_substr($text, $start), $length - 1);
    }

    // ------------------------------------------------------------------

    protected function analyse(): void
    {
        $totals = array_fill_keys(self::FIELDS, 0);

        foreach ($this->documents as $i => $document) {
            foreach (self::FIELDS as $field) {
                $raw = SearchText::tokens((string) ($document[$field] ?? ''), false);
                $this->lengths[$i][$field] = count(array_unique($raw));
                $totals[$field] += $this->lengths[$i][$field];

                foreach (SearchText::tokens((string) ($document[$field] ?? '')) as $term) {
                    $this->postings[$field][$term][$i] = ($this->postings[$field][$term][$i] ?? 0) + 1;
                    $this->known[$term] = true;
                }
            }
        }

        $count = max(1, count($this->documents));
        foreach ($totals as $field => $total) {
            $this->averages[$field] = $total / $count;
        }
    }

    /**
     * Combine les sous-requêtes par mot (ET ou OU), chaque mot élargi à ses
     * synonymes, exactement comme l'arbre de requête envoyé à MiniSearch.
     *
     * @param  list<string>  $terms
     * @return array<int, array{document: array<string, mixed>, score: float, terms: list<string>}>
     */
    protected function combine(array $terms, string $combineWith, bool $fullPrefix): array
    {
        $combined = null;

        foreach ($terms as $term) {
            $partial = $this->subquery($term, $fullPrefix);

            if ($combined === null) {
                $combined = $partial;

                continue;
            }

            if ($combineWith === 'AND') {
                $next = [];
                foreach ($partial as $doc => $hit) {
                    if (isset($combined[$doc])) {
                        $next[$doc] = [
                            'score' => $combined[$doc]['score'] + $hit['score'],
                            'terms' => array_values(array_unique(array_merge($combined[$doc]['terms'], $hit['terms']))),
                        ];
                    }
                }
                $combined = $next;
            } else {
                foreach ($partial as $doc => $hit) {
                    if (isset($combined[$doc])) {
                        $combined[$doc]['score'] += $hit['score'];
                        $combined[$doc]['terms'] = array_values(array_unique(array_merge($combined[$doc]['terms'], $hit['terms'])));
                    } else {
                        $combined[$doc] = $hit;
                    }
                }
            }
        }

        $results = [];
        foreach ($combined ?? [] as $doc => $hit) {
            // Comme MiniSearch : un document qui matche plusieurs mots (ou synonymes) est multiplié d'autant.
            $results[$doc] = [
                'document' => $this->documents[$doc],
                'score' => $hit['score'] * max(1, count($hit['terms'])),
                'terms' => $hit['terms'],
            ];
        }

        return $results;
    }

    /**
     * Un mot et ses synonymes : exact, préfixes, fautes, scores additionnés.
     *
     * @return array<int, array{score: float, terms: list<string>}>
     */
    protected function subquery(string $term, bool $fullPrefix): array
    {
        $results = [];
        $fuzzyMin = (int) ($this->rules['fuzzy_min_chars'] ?? 5);

        $words = [$term => 1.0];
        foreach ($this->synonymsOf($term) as $synonym) {
            $words[$synonym] ??= self::SYNONYM_WEIGHT;
        }

        foreach ($words as $word => $termBoost) {
            $word = (string) $word;
            $this->score($results, $word, $word, 1.0, $termBoost);

            $prefixed = [];
            if ($fullPrefix || ! $this->isKnown($word)) {
                foreach (array_keys($this->known) as $candidate) {
                    $candidate = (string) $candidate;
                    if ($candidate !== $word && str_starts_with($candidate, $word)) {
                        $prefixed[$candidate] = true;
                        $distance = strlen($candidate) - strlen($word);
                        $weight = self::PREFIX_WEIGHT * strlen($candidate) / (strlen($candidate) + 0.3 * $distance);
                        $this->score($results, $word, $candidate, $weight, $termBoost);
                    }
                }
            }

            if (strlen($word) >= $fuzzyMin) {
                foreach (array_keys($this->known) as $candidate) {
                    $candidate = (string) $candidate;
                    if ($candidate === $word || isset($prefixed[$candidate]) || abs(strlen($candidate) - strlen($word)) > 1) {
                        continue;
                    }
                    if (levenshtein($candidate, $word) === 1) {
                        $weight = self::FUZZY_WEIGHT * strlen($candidate) / (strlen($candidate) + 1);
                        $this->score($results, $word, $candidate, $weight, $termBoost);
                    }
                }
            }
        }

        return $results;
    }

    /**
     * BM25 de MiniSearch pour un mot dérivé, champ par champ.
     *
     * @param  array<int, array{score: float, terms: list<string>}>  $results
     */
    protected function score(array &$results, string $source, string $word, float $weight, float $termBoost): void
    {
        $boosts = (array) ($this->rules['boost'] ?? []);
        $total = count($this->documents);

        foreach (self::FIELDS as $field) {
            $postings = $this->postings[$field][$word] ?? null;
            if ($postings === null) {
                continue;
            }

            $matching = count($postings);
            $idf = log(1 + ($total - $matching + 0.5) / ($matching + 0.5));
            $average = $this->averages[$field] ?: 1;

            foreach ($postings as $doc => $tf) {
                $length = $this->lengths[$doc][$field];
                $raw = $idf * (self::BM25_D + $tf * (self::BM25_K + 1) / ($tf + self::BM25_K * (1 - self::BM25_B + self::BM25_B * $length / $average)));
                $value = $weight * $termBoost * ($boosts[$field] ?? 1) * $raw;

                if (isset($results[$doc])) {
                    $results[$doc]['score'] += $value;
                    if (! in_array($source, $results[$doc]['terms'], true)) {
                        $results[$doc]['terms'][] = $source;
                    }
                } else {
                    $results[$doc] = ['score' => $value, 'terms' => [$source]];
                }
            }
        }
    }

    /**
     * @param  array<int, array{document: array<string, mixed>, score: float, terms: list<string>}>  $results
     * @return list<array{document: array<string, mixed>, score: float, terms: list<string>}>
     */
    protected function sorted(array $results): array
    {
        uksort($results, fn ($a, $b) => $results[$b]['score'] <=> $results[$a]['score'] ?: $a <=> $b);

        return array_values($results);
    }

    protected function isKnown(string $term): bool
    {
        return strlen($term) >= (int) ($this->rules['known_word']['min_chars'] ?? 4) && isset($this->known[$term]);
    }

    /**
     * @param  list<string>  $terms
     */
    protected function hasKnown(array $terms): bool
    {
        foreach ($terms as $term) {
            if ($this->isKnown($term)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    protected function synonymsOf(string $term): array
    {
        $found = [];

        foreach ((array) ($this->rules['synonyms'] ?? []) as $group) {
            $single = [];
            $others = [];

            foreach ($group as $word) {
                $tokens = SearchText::tokens((string) $word);
                if (count($tokens) === 1) {
                    $single[$tokens[0]] = true;
                }
                foreach ($tokens as $token) {
                    $others[] = $token;
                }
            }

            if (! isset($single[$term])) {
                continue;
            }

            foreach ($others as $token) {
                if ($token !== $term) {
                    $found[] = $token;
                }
            }
        }

        return array_values(array_unique($found));
    }
}
