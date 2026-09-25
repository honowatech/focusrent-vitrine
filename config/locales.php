<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default locale
    |--------------------------------------------------------------------------
    |
    | Served without a URL prefix (e.g. /about). Other locales are prefixed
    | (e.g. /en/about). Change this only if you also redirect old URLs.
    |
    */

    'default' => 'fr',

    /*
    |--------------------------------------------------------------------------
    | Available locales
    |--------------------------------------------------------------------------
    |
    | To add a language:
    |  1. Add an entry below.
    |  2. Copy lang/fr to lang/{code} and translate the PHP files.
    | Routes, hreflang, sitemap, language switcher and llms.txt update
    | themselves from this list. No other code change is required.
    |
    */

    'available' => [
        'fr' => [
            'name' => 'Français',
            'native' => 'Français',
            'regional' => 'fr_FR',
            'hreflang' => 'fr',
            'dir' => 'ltr',
        ],
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'regional' => 'en_US',
            'hreflang' => 'en',
            'dir' => 'ltr',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Public HTML pages (route names => path)
    |--------------------------------------------------------------------------
    */

    'pages' => [
        'index' => '/',
        'pricing' => '/tarifs',
        'about' => '/about',
        'faq' => '/faq',
        'contact' => '/contact',
        'privacy' => '/privacy',
        'terms' => '/terms',
    ],

];
