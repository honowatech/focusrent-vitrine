<?php

namespace App\Providers;

use App\Support\SuperAdmin;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole() || config('app.asset_url_locked')) {
            return;
        }

        // Front controller is /public/index.php only when the document root is
        // the project root. Otherwise /public/... 404s and the site has no CSS.
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($script !== '' && ! str_contains($script, '/public/')) {
            $this->app['config']->set('app.asset_url', null);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(MigrationsEnded::class, function () {
            SuperAdmin::ensure();
        });

        SuperAdmin::ensure();
    }
}
