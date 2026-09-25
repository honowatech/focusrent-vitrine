<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSiteMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! SiteSetting::current()->isMaintenance() || auth()->check()) {
            return $next($request);
        }

        return response()->view('maintenance', [], 503);
    }
}
