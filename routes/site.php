<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::middleware('site.mode')->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('index');
    Route::get('/tarifs', [PageController::class, 'pricing'])->name('pricing');
    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/faq', [PageController::class, 'faq'])->name('faq');
    Route::get('/contact', [PageController::class, 'contact'])->name('contact');
    Route::post('/contact', [ContactController::class, 'sendMail'])->name('contact.send');
    Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
    Route::get('/terms', [PageController::class, 'terms'])->name('terms');
    Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');
    Route::get('/llms-full.txt', [SeoController::class, 'llmsFull'])->name('llms-full');
});
