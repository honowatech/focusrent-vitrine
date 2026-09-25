<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class PwaController extends Controller
{
    public function manifest(): Response
    {
        $icon192 = asset('images/pwa/admin-192.png');
        $icon512 = asset('images/pwa/admin-512.png');
        $maskable = asset('images/pwa/admin-maskable-512.png');

        $manifest = [
            'id' => '/admin',
            'name' => 'Focus Rent Admin',
            'short_name' => 'FR Admin',
            'description' => 'Suivi des visites et des messages du site Focus Rent.',
            'start_url' => '/admin',
            'scope' => '/admin',
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => '#0A0413',
            'theme_color' => '#0A0413',
            'lang' => 'fr',
            'icons' => [
                ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => $maskable, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                ['name' => 'Visites', 'short_name' => 'Visites', 'url' => '/admin'],
                ['name' => 'Messages', 'short_name' => 'Messages', 'url' => '/admin/messages'],
            ],
        ];

        return response(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'no-cache',
        ]);
    }

    public function serviceWorker(): Response
    {
        $path = resource_path('pwa/admin-sw.js');

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Service-Worker-Allowed' => '/admin',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
