<?php

namespace App\Support;

/**
 * Données partagées par le <dialog> de la palette et la page de résultats.
 */
class SearchPresenter
{
    /**
     * @return array<string, string>
     */
    public static function icons(): array
    {
        return [
            'feature' => 'bolt',
            'plan' => 'file-invoice',
            'faq' => 'question-circle',
            'page' => 'folder-open',
        ];
    }

    public static function icon(string $type): string
    {
        return self::icons()[$type] ?? 'folder-open';
    }

    /**
     * Libellés injectés dans la palette (data-search-labels).
     *
     * @return array<string, mixed>
     */
    public static function labels(): array
    {
        return [
            'groups' => (array) __('search.groups'),
            'status' => (array) __('search.status'),
            'initial' => (array) __('search.initial'),
            'empty' => (array) __('search.empty'),
            'error' => (array) __('search.error'),
            'see_all' => (string) __('search.see_all'),
            'clear' => (string) __('search.clear'),
            'loading' => (string) __('search.loading'),
            'per_month' => (string) __('search.meta.per_month'),
            'popular' => (array) __('search.popular_terms'),
        ];
    }

    /**
     * Règles de recherche injectées dans la palette (data-search-rules).
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        $rules = (array) config('search');

        return [
            'min_chars' => $rules['min_chars'],
            'max_chars' => $rules['max_chars'],
            'per_group' => $rules['per_group'],
            'groups' => $rules['groups'],
            'boost' => $rules['boost'],
            'fuzzy_min_chars' => $rules['fuzzy_min_chars'],
            'known_word' => $rules['known_word'],
            // Sérialisés en « a|b|c » : pas de tableaux imbriqués dans un attribut.
            'synonyms' => array_map(fn (array $group) => implode('|', $group), $rules['synonyms']),
            'stop_words' => $rules['stop_words'],
        ];
    }

    /**
     * Catégories proposées dans l'état initial de la palette.
     *
     * @return list<array{label: string, url: string, icon: string}>
     */
    public static function categories(): array
    {
        return [
            ['label' => (string) __('search.groups.feature'), 'url' => locale_route('index').'#fonctionnalites', 'icon' => 'bolt'],
            ['label' => (string) __('search.groups.plan'), 'url' => locale_route('pricing'), 'icon' => 'file-invoice'],
            ['label' => (string) __('search.groups.faq'), 'url' => locale_route('faq'), 'icon' => 'question-circle'],
            ['label' => (string) __('site.nav.contact'), 'url' => locale_route('contact'), 'icon' => 'envelope'],
        ];
    }
}
