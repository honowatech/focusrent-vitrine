<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Identité anonyme d'une visite : empreinte de session, jamais d'adresse IP.
 */
class Visitor
{
    public static function isBot(?string $agent): bool
    {
        $agent = (string) $agent;

        return $agent === ''
            || (bool) preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram|headless|lighthouse|curl|wget|python-requests/i', $agent);
    }

    public static function id(Request $request): ?string
    {
        try {
            if ($request->hasSession()) {
                return hash('sha256', $request->session()->getId());
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
