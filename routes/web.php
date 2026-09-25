<?php

use App\Http\Controllers\SeoController;
use App\Support\Locales;
use Illuminate\Support\Facades\Route;

require __DIR__.'/site.php';

foreach (Locales::prefixed() as $locale) {
    Route::prefix($locale)
        ->as($locale.'.')
        ->group(__DIR__.'/site.php');
}

Route::redirect('/pricing', '/tarifs', 301);
Route::redirect('/en/pricing', '/en/tarifs', 301);
Route::redirect('/fr/pricing', '/tarifs', 301);

require __DIR__.'/admin.php';

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

$default = Locales::default();
Route::get($default, [SeoController::class, 'redirectDefaultPrefix']);
Route::get($default.'/{path}', [SeoController::class, 'redirectDefaultPrefix'])->where('path', '.*');
