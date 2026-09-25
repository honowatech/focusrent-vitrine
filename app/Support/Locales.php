<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class Locales
{
    public static function default(): string
    {
        return (string) config('locales.default', 'fr');
    }

    public static function available(): array
    {
        return (array) config('locales.available', []);
    }

    public static function codes(): array
    {
        return array_keys(self::available());
    }

    public static function prefixed(): array
    {
        return array_values(array_filter(
            self::codes(),
            fn (string $code) => $code !== self::default()
        ));
    }

    public static function isSupported(string $locale): bool
    {
        return array_key_exists($locale, self::available());
    }

    public static function meta(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $available = self::available();

        return $available[$locale] ?? $available[self::default()] ?? [
            'name' => $locale,
            'native' => $locale,
            'regional' => $locale,
            'hreflang' => $locale,
            'dir' => 'ltr',
        ];
    }

    public static function pages(): array
    {
        return (array) config('locales.pages', []);
    }

    public static function currentPage(): string
    {
        $name = Route::currentRouteName() ?: 'index';

        if (preg_match('/^([a-z]{2})\.(.+)$/', $name, $matches) && self::isSupported($matches[1])) {
            return $matches[2];
        }

        return $name;
    }

    public static function routeName(string $page, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if (! self::isSupported($locale)) {
            $locale = self::default();
        }

        $name = $locale === self::default() ? $page : $locale.'.'.$page;

        return Route::has($name) ? $name : $page;
    }

    public static function route(string $page, array $parameters = [], ?string $locale = null, bool $absolute = true): string
    {
        return route(self::routeName($page, $locale), $parameters, $absolute);
    }

    public static function routeIs(string ...$pages): bool
    {
        return in_array(self::currentPage(), $pages, true);
    }

    public static function switch(string $locale): string
    {
        $page = self::currentPage();

        if (! array_key_exists($page, self::pages()) && ! in_array($page, ['llms', 'llms-full'], true)) {
            $page = 'index';
        }

        return self::route($page, [], $locale);
    }

    public static function hreflang(string $page): array
    {
        $tags = [];

        foreach (self::codes() as $code) {
            $tags[] = [
                'hreflang' => self::available()[$code]['hreflang'] ?? $code,
                'href' => self::route($page, [], $code),
            ];
        }

        $tags[] = [
            'hreflang' => 'x-default',
            'href' => self::route($page, [], self::default()),
        ];

        return $tags;
    }

    public static function pathFor(string $page, string $locale): string
    {
        $path = self::pages()[$page] ?? '/';
        $path = '/'.ltrim($path, '/');

        if ($path === '/') {
            return $locale === self::default() ? '/' : '/'.$locale;
        }

        return $locale === self::default() ? $path : '/'.$locale.$path;
    }
}
