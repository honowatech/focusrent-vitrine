<?php

use App\Support\Locales;

if (! function_exists('locale_route')) {
    function locale_route(string $page, array $parameters = [], ?string $locale = null, bool $absolute = true): string
    {
        return Locales::route($page, $parameters, $locale, $absolute);
    }
}

if (! function_exists('locale_route_is')) {
    function locale_route_is(string ...$pages): bool
    {
        return Locales::routeIs(...$pages);
    }
}

if (! function_exists('locale_switch')) {
    function locale_switch(string $locale): string
    {
        return Locales::switch($locale);
    }
}
