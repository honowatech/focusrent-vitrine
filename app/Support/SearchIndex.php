<?php

namespace App\Support;

/**
 * Construit la liste des documents de l'index de recherche d'une langue,
 * à partir des fichiers lang déjà rendus par le site.
 *
 * Un document de tête par page, puis un document par section ancrée.
 * Les prix ne vivent que dans les fiches « plan » (métadonnées typées).
 */
class SearchIndex
{
    public const EXCERPT_LENGTH = 160;

    public const BODY_LENGTH = 1500;

    /**
     * @return array{locale: string, documents: list<array<string, mixed>>}
     */
    public static function build(string $locale): array
    {
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            $documents = array_merge(
                self::features($locale),
                self::plans($locale),
                self::faqs($locale),
                self::pages($locale),
            );
        } finally {
            app()->setLocale($previous);
        }

        return [
            'locale' => $locale,
            'generated_at' => gmdate('c'),
            'documents' => array_values($documents),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected static function features(string $locale): array
    {
        $documents = [];
        $url = Locales::pathFor('index', $locale);
        $domain = (string) __('home.features.title');

        foreach ((array) __('home.features.items') as $item) {
            $documents[] = self::document([
                'id' => 'feature:'.$item['icon'],
                'type' => 'feature',
                'title' => $item['title'],
                'section' => '',
                'label' => $item['title'],
                'url' => $url.'#feature-'.$item['icon'],
                'breadcrumb' => [(string) __('seo.index.nav'), $domain],
                'excerpt' => $item['text'],
                'body' => $item['text'],
                'domain' => $domain,
            ]);
        }

        return $documents;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected static function plans(string $locale): array
    {
        $documents = [];
        $url = Locales::pathFor('pricing', $locale);
        $domain = (string) __('pricing.plans_title');
        $period = (string) __('home.pricing.period');
        $currency = (string) __('home.pricing.currency');

        foreach ((array) __('home.pricing.plans') as $plan) {
            $key = strtolower($plan['name']);
            $features = implode('. ', $plan['features']).'.';

            $documents[] = self::document([
                'id' => 'plan:'.$key,
                'type' => 'plan',
                'title' => $plan['name'],
                'section' => '',
                'label' => $plan['name'],
                'url' => $url.'#'.$key,
                'breadcrumb' => [(string) __('seo.pricing.nav'), $domain],
                'excerpt' => $plan['units'].'. '.$plan['users'].'.',
                'body' => $plan['units'].'. '.$plan['users'].'. '.$features.' '.__('pricing.payment_value').'.',
                'domain' => $domain,
                'price' => $plan['price'].' '.$currency.' '.$period,
                'units' => $plan['units'],
                'users' => $plan['users'],
            ], stripPrices: false);
        }

        return $documents;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected static function faqs(string $locale): array
    {
        $documents = [];

        $sources = [
            ['page' => 'faq', 'key' => 'general', 'items' => (array) __('faq.items'), 'domain' => (string) __('faq.title')],
            ['page' => 'pricing', 'key' => 'pricing', 'items' => (array) __('pricing.faq'), 'domain' => (string) __('pricing.faq_title')],
        ];

        foreach ($sources as $source) {
            $url = Locales::pathFor($source['page'], $locale);
            $position = 0;

            foreach ($source['items'] as $item) {
                $position++;

                $documents[] = self::document([
                    'id' => 'faq:'.$source['key'].'-'.$position,
                    'type' => 'faq',
                    'title' => $item['q'],
                    'section' => '',
                    'label' => $item['q'],
                    'url' => $url.'#faq-'.$position,
                    'breadcrumb' => [(string) __('seo.'.$source['page'].'.nav'), $source['domain']],
                    'excerpt' => $item['a'],
                    'body' => $item['a'],
                    'domain' => $source['domain'],
                ]);
            }
        }

        return $documents;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected static function pages(string $locale): array
    {
        $documents = [];

        foreach (self::pageSections() as $page => $sections) {
            $url = Locales::pathFor($page, $locale);
            $title = (string) __('seo.'.$page.'.nav');
            $breadcrumb = $page === 'index' ? [] : [(string) __('seo.index.nav')];
            $description = (string) __('seo.'.$page.'.description');

            $documents[] = self::document([
                'id' => 'page:'.$page,
                'type' => 'page',
                'title' => $title,
                'section' => '',
                'label' => $title,
                'url' => $url,
                'breadcrumb' => $breadcrumb,
                'excerpt' => $description,
                'body' => $description.' '.implode(' ', array_map(fn (array $section) => $section['title'], $sections)),
                'domain' => $title,
            ]);

            foreach ($sections as $anchor => $section) {
                if (trim($section['body']) === '') {
                    continue;
                }

                $documents[] = self::document([
                    'id' => 'page:'.$page.'#'.$anchor,
                    'type' => 'page',
                    'title' => $title,
                    'section' => $section['title'],
                    'label' => $section['title'],
                    'url' => $url.'#'.$anchor,
                    'breadcrumb' => array_merge($breadcrumb, [$title]),
                    'excerpt' => $section['body'],
                    'body' => $section['body'],
                    'domain' => $title,
                ]);
            }
        }

        return $documents;
    }

    /**
     * Sections ancrées de chaque page statique : ancre => [title, body].
     * Les blocs déjà indexés ailleurs (fonctionnalités, formules, FAQ) sont écartés.
     *
     * @return array<string, array<string, array{title: string, body: string}>>
     */
    protected static function pageSections(): array
    {
        $items = fn (array $items) => implode(' ', array_map(
            fn (array $item) => trim(($item['title'] ?? '').'. '.($item['text'] ?? '')),
            $items
        ));

        $pages = [
            'index' => [
                'pourquoi' => [
                    'title' => __('home.why.title'),
                    'body' => $items((array) __('home.why.items')),
                ],
                'galerie' => [
                    'title' => __('home.gallery.title'),
                    'body' => implode(' ', array_map(fn (array $slide) => $slide['alt'], (array) __('home.gallery.slides'))),
                ],
                'temoignages' => [
                    'title' => __('home.testimonials.title'),
                    'body' => implode(' ', array_map(fn (array $item) => $item['quote'].' '.$item['author'], (array) __('home.testimonials.items'))),
                ],
            ],
            'about' => [
                'hero' => [
                    'title' => __('about.hero.title'),
                    'body' => __('about.hero.subtitle').' '.implode('. ', (array) __('about.hero.badges')).'.',
                ],
                'parcours' => [
                    'title' => __('about.timeline.title'),
                    'body' => $items((array) __('about.timeline.items')),
                ],
                'mission' => [
                    'title' => __('about.mission.title'),
                    'body' => __('about.mission.heading').'. '.__('about.mission.p1').' '.__('about.mission.p2'),
                ],
                'valeurs' => [
                    'title' => __('about.values.title'),
                    'body' => $items((array) __('about.values.items')),
                ],
                'vision' => [
                    'title' => __('about.vision.title'),
                    'body' => __('about.vision.lead').' '.__('about.vision.text'),
                ],
                'demo' => [
                    'title' => __('about.cta.title'),
                    'body' => __('about.cta.demo').'. '.__('about.cta.pricing').'.',
                ],
            ],
            'pricing' => [
                'intro' => [
                    'title' => __('pricing.title'),
                    'body' => __('pricing.lead'),
                ],
                'compare-title' => [
                    'title' => __('pricing.compare_title'),
                    'body' => __('pricing.compare_caption').' '.__('pricing.row_payment').' : '.__('pricing.payment_value').'.',
                ],
                'billing-title' => [
                    'title' => __('pricing.billing_title'),
                    'body' => implode(' ', (array) __('pricing.billing')),
                ],
                'pricing-cta-title' => [
                    'title' => __('pricing.cta_title'),
                    'body' => __('pricing.cta_text'),
                ],
            ],
            'contact' => [
                'contact' => [
                    'title' => __('contact.title'),
                    'body' => __('contact.hint').' '.__('site.phone').' '.__('site.email').' '.__('site.footer.city').'.',
                ],
            ],
        ];

        foreach (['privacy', 'terms'] as $legal) {
            $pages[$legal] = [];
            $position = 0;

            foreach ((array) __($legal.'.sections') as $section) {
                $position++;
                $parts = [];

                foreach ((array) ($section['blocks'] ?? []) as $block) {
                    if (($block['type'] ?? 'p') === 'ul') {
                        $parts[] = implode(' ', (array) ($block['items'] ?? []));
                    } else {
                        $parts[] = (string) ($block['text'] ?? '');
                    }
                }

                $pages[$legal]['section-'.$position] = [
                    'title' => (string) $section['title'],
                    'body' => implode(' ', $parts),
                ];
            }
        }

        return $pages;
    }

    /**
     * Normalise un document : texte brut, prix retirés, longueurs bornées.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function document(array $data, bool $stripPrices = true): array
    {
        $excerpt = SearchText::clean((string) $data['excerpt']);
        $body = SearchText::clean((string) $data['body']);

        if ($stripPrices) {
            $excerpt = SearchText::withoutPrices($excerpt);
            $body = SearchText::withoutPrices($body);
        }

        if ($excerpt === '') {
            $excerpt = $body;
        }

        return array_merge([
            'id' => '',
            'type' => 'page',
            'title' => '',
            'section' => '',
            'label' => '',
            'url' => '/',
            'breadcrumb' => [],
            'excerpt' => '',
            'body' => '',
            'domain' => '',
        ], $data, [
            'title' => SearchText::clean((string) $data['title']),
            'section' => SearchText::clean((string) ($data['section'] ?? '')),
            'label' => SearchText::clean((string) $data['label']),
            'breadcrumb' => array_values(array_map(fn ($crumb) => SearchText::clean((string) $crumb), (array) $data['breadcrumb'])),
            'excerpt' => SearchText::limit($excerpt, self::EXCERPT_LENGTH),
            'body' => SearchText::limit($body, self::BODY_LENGTH),
        ]);
    }

    public static function path(string $locale): string
    {
        return public_path('search/'.$locale.'.json');
    }

    /**
     * Lit l'index écrit sur disque, ou le construit à la volée s'il manque.
     *
     * @return array{locale: string, documents: list<array<string, mixed>>}
     */
    public static function load(string $locale): array
    {
        $path = self::path($locale);

        if (is_file($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);

            if (is_array($decoded) && isset($decoded['documents'])) {
                return $decoded;
            }
        }

        return self::build($locale);
    }

    public static function version(string $locale): string
    {
        $path = self::path($locale);

        return is_file($path) ? (string) filemtime($path) : '0';
    }
}
