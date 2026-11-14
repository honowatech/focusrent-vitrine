<?php

use App\Http\Controllers\SearchController;
use App\Http\Controllers\SearchTrackController;
use App\Http\Controllers\SeoController;
use App\Support\Locales;
use Illuminate\Support\Facades\Route;

require __DIR__.'/site.php';

foreach (Locales::prefixed() as $locale) {
    Route::prefix($locale)
        ->as($locale.'.')
        ->group(__DIR__.'/site.php');
}

foreach (Locales::codes() as $locale) {
    $prefix = $locale === Locales::default() ? '' : '/'.$locale;
    $name = $locale === Locales::default() ? 'search' : $locale.'.search';

    Route::get($prefix.'/'.Locales::searchSlug($locale), [SearchController::class, 'show'])
        ->middleware('site.mode')
        ->name($name);
}

Route::post('/search/track', [SearchTrackController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('search.track');

Route::get('/search/go', [SearchTrackController::class, 'go'])->name('search.go');

Route::redirect('/pricing', '/tarifs', 301);
Route::redirect('/en/pricing', '/en/tarifs', 301);
Route::redirect('/fr/pricing', '/tarifs', 301);

require __DIR__.'/admin.php';

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

$default = Locales::default();
Route::get($default, [SeoController::class, 'redirectDefaultPrefix']);
Route::get($default.'/{path}', [SeoController::class, 'redirectDefaultPrefix'])->where('path', '.*');
