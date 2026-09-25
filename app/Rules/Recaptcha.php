<?php

namespace App\Rules;

use App\Models\SiteSetting;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Recaptcha implements ImplicitRule
{
    /**
     * Rule implicite : la vérification s'exécute même lorsque le champ
     * g-recaptcha-response est absent de la requête (soumission de bot).
     */
    public function passes($attribute, $value): bool
    {
        if (! static::enabled()) {
            return true;
        }

        $response = Http::get('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $value,
        ])->json();

        if (! (is_array($response) && ($response['success'] ?? false))) {
            Log::warning('reCAPTCHA verification failed.', ['response' => $response]);

            return false;
        }

        return true;
    }

    public function message(): array
    {
        return [__('contact.recaptcha_invalid')];
    }

    /**
     * Le widget et la vérification ne sont actifs qu'en production effective :
     * le mode développement (back-office) ou le flag local les court-circuitent.
     */
    public static function enabled(): bool
    {
        return ! config('services.recaptcha.skip')
            && ! SiteSetting::current()->isDevelopment()
            && filled(config('services.recaptcha.key'));
    }
}
