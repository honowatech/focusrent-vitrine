<?php

namespace App\Support;

class Sitemap
{
    public static function entries(): array
    {
        $entries = [];

        foreach (array_keys(Locales::pages()) as $page) {
            foreach (Locales::codes() as $locale) {
                $entries[] = [
                    'loc' => Locales::route($page, [], $locale),
                    'lastmod' => self::lastmod($page, $locale),
                    'changefreq' => self::changefreq($page),
                    'priority' => self::priority($page),
                    'alternates' => Locales::hreflang($page),
                    'images' => $page === 'index' ? self::images() : [],
                ];
            }
        }

        return $entries;
    }

    public static function lastmod(string $page, string $locale): string
    {
        $files = [
            resource_path('views/'.$page.'.blade.php'),
            resource_path('views/components/layout.blade.php'),
            resource_path('views/components/header.blade.php'),
            resource_path('views/components/footer.blade.php'),
            lang_path($locale.'/seo.php'),
            lang_path($locale.'/site.php'),
            lang_path($locale.'/'.$page.'.php'),
        ];

        if (in_array($page, ['index', 'pricing'], true)) {
            $files[] = lang_path($locale.'/home.php');
        }

        if (in_array($page, ['privacy', 'terms'], true)) {
            $files[] = resource_path('views/components/legal-page.blade.php');
        }

        $mtime = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                $mtime = max($mtime, (int) filemtime($file));
            }
        }

        return gmdate('Y-m-d', $mtime ?: time());
    }

    public static function changefreq(string $page): string
    {
        return match ($page) {
            'index' => 'weekly',
            'pricing', 'about', 'faq', 'contact' => 'monthly',
            default => 'yearly',
        };
    }

    public static function priority(string $page): string
    {
        return match ($page) {
            'index' => '1.0',
            'pricing' => '0.9',
            'about', 'contact' => '0.8',
            'faq' => '0.7',
            default => '0.4',
        };
    }

    public static function images(): array
    {
        return [
            [
                'loc' => asset('images/og-image.png'),
                'title' => 'Focus Rent',
            ],
            [
                'loc' => asset('images/hero-bg.webp'),
                'title' => 'Focus Rent',
            ],
            [
                'loc' => asset('images/all_stats.webp'),
                'title' => 'Focus Rent dashboard',
            ],
        ];
    }
}
