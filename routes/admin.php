<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PwaController;
use App\Http\Controllers\Admin\SiteModeController;
use App\Http\Controllers\Admin\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/manifest.webmanifest', [PwaController::class, 'manifest'])->name('admin.manifest');
Route::get('/admin/sw.js', [PwaController::class, 'serviceWorker'])->name('admin.sw');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'show'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/admin/setup', [AuthController::class, 'setup'])->middleware('throttle:5,1');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [VisitController::class, 'index'])->name('visits');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::get('/mail', [MailSettingController::class, 'edit'])->name('mail');
    Route::post('/mail', [MailSettingController::class, 'update'])->name('mail.update');
    Route::get('/mode', [SiteModeController::class, 'edit'])->name('mode');
    Route::post('/mode', [SiteModeController::class, 'update'])->name('mode.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
