<?php

namespace App\Support;

class Seo
{
    public static function page(): string
    {
        $page = Locales::currentPage();

        return array_key_exists($page, Locales::pages()) ? $page : 'index';
    }

    public static function title(?string $override = null): string
    {
        return $override ?: (string) __('seo.'.self::page().'.title');
    }

    public static function description(?string $override = null): string
    {
        return $override ?: (string) __('seo.'.self::page().'.description');
    }

    public static function canonical(): string
    {
        return locale_route(self::page());
    }

    public static function ogImage(): string
    {
        return asset('images/og-image.png');
    }

    public static function jsonLd(): array
    {
        $page = self::page();
        $locale = app()->getLocale();
        $meta = Locales::meta($locale);
        $home = locale_route('index');
        $orgId = rtrim(config('app.url'), '/').'/#organization';
        $appId = rtrim(config('app.url'), '/').'/#software';
        $websiteId = rtrim(config('app.url'), '/').'/#website';

        $organization = [
            '@type' => 'Organization',
            '@id' => $orgId,
            'name' => 'Focus Rent',
            'legalName' => 'Honowa Technologies',
            'url' => $home,
            'logo' => asset('images/logo.svg'),
            'email' => 'contact@focusrent.cm',
            'telephone' => '+237674970806',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Douala',
                'addressCountry' => 'CM',
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'Cameroon',
            ],
            'sameAs' => [
                'https://facebook.com/Tech.Honowa',
                'https://twitter.com/honowa6',
                'https://cm.linkedin.com/company/honowa-technologies',
            ],
        ];

        $website = [
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'url' => $home,
            'name' => 'Focus Rent',
            'inLanguage' => array_values(array_map(
                fn (array $item) => $item['hreflang'],
                Locales::available()
            )),
            'publisher' => ['@id' => $orgId],
        ];

        $software = [
            '@type' => 'SoftwareApplication',
            '@id' => $appId,
            'name' => 'Focus Rent',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web, Android, iOS',
            'url' => $home,
            'description' => self::description(),
            'inLanguage' => $meta['hreflang'],
            'offers' => [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'XAF',
                'lowPrice' => '10000',
                'highPrice' => '59000',
                'offerCount' => 3,
                'url' => locale_route('pricing'),
                'offers' => self::planOffers(),
            ],
            'featureList' => array_map(
                fn (array $item) => $item['title'],
                __('home.features.items')
            ),
            'provider' => ['@id' => $orgId],
        ];

        $webPage = [
            '@type' => self::webPageType($page),
            '@id' => self::canonical().'#webpage',
            'url' => self::canonical(),
            'name' => self::title(),
            'description' => self::description(),
            'inLanguage' => $meta['hreflang'],
            'isPartOf' => ['@id' => $websiteId],
            'about' => ['@id' => $appId],
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                '@id' => self::canonical().'#breadcrumb',
                'itemListElement' => self::breadcrumbs($page),
            ],
        ];

        if ($page === 'pricing') {
            $webPage['mainEntity'] = ['@id' => self::canonical().'#offers'];
        }

        $graph = [$organization, $website, $software, $webPage];

        if ($page === 'faq') {
            $graph[] = [
                '@type' => 'FAQPage',
                '@id' => self::canonical().'#faq',
                'inLanguage' => $meta['hreflang'],
                'mainEntity' => array_map(function (array $item) {
                    return [
                        '@type' => 'Question',
                        'name' => $item['q'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $item['a'],
                        ],
                    ];
                }, __('faq.items')),
            ];
        }

        if (in_array($page, ['index', 'pricing'], true)) {
            $graph[] = [
                '@type' => 'OfferCatalog',
                '@id' => locale_route('pricing').'#offers',
                'name' => __('pricing.title'),
                'url' => locale_route('pricing'),
                'itemListElement' => self::planOffers(),
            ];
        }

        if ($page === 'pricing') {
            $graph[] = [
                '@type' => 'FAQPage',
                '@id' => self::canonical().'#faq',
                'url' => self::canonical().'#faq',
                'inLanguage' => $meta['hreflang'],
                'isPartOf' => ['@id' => self::canonical().'#webpage'],
                'mainEntity' => array_map(function (array $item) {
                    return [
                        '@type' => 'Question',
                        'name' => $item['q'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $item['a'],
                        ],
                    ];
                }, __('pricing.faq')),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    protected static function webPageType(string $page): string
    {
        return match ($page) {
            'about' => 'AboutPage',
            'contact' => 'ContactPage',
            'faq' => 'WebPage',
            'privacy', 'terms' => 'WebPage',
            default => 'WebPage',
        };
    }

    protected static function breadcrumbs(string $page): array
    {
        $items = [[
            '@type' => 'ListItem',
            'position' => 1,
            'name' => __('site.nav.home'),
            'item' => locale_route('index'),
        ]];

        if ($page !== 'index') {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => __('seo.'.$page.'.nav'),
                'item' => self::canonical(),
            ];
        }

        return $items;
    }

    protected static function planOffers(): array
    {
        $seller = rtrim(config('app.url'), '/').'/#organization';

        return array_map(function (array $plan) use ($seller) {
            $anchor = locale_route('pricing').'#'.strtolower($plan['name']);

            return [
                '@type' => 'Offer',
                'name' => 'Focus Rent '.$plan['name'],
                'url' => $anchor,
                'price' => $plan['price_value'],
                'priceCurrency' => 'XAF',
                'availability' => 'https://schema.org/InStock',
                'description' => $plan['units'].'. '.$plan['users'].'. '.implode('; ', $plan['features']),
                'seller' => ['@id' => $seller],
                'priceSpecification' => [
                    '@type' => 'UnitPriceSpecification',
                    'price' => $plan['price_value'],
                    'priceCurrency' => 'XAF',
                    'unitText' => 'MONTH',
                    'valueAddedTaxIncluded' => false,
                ],
            ];
        }, __('home.pricing.plans'));
    }
}
