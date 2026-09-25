<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class RecordPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldRecord($request, $response)) {
            return $response;
        }

        $page = Locales::currentPage();
        if (! array_key_exists($page, Locales::pages())) {
            $page = null;
        }

        try {
            PageView::create([
                'path' => '/'.ltrim($request->path(), '/'),
                'page' => $page,
                'locale' => app()->getLocale(),
                'viewed_on' => now()->timezone('Africa/Douala')->toDateString(),
                'visitor' => hash('sha256', $request->session()->getId()),
            ]);
        } catch (\Throwable) {
            return $response;
        }

        return $response;
    }

    protected function shouldRecord(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return false;
        }

        if ($request->is('admin', 'admin/*')) {
            return false;
        }

        if ($request->headers->has('Purpose') || $request->headers->has('Sec-Purpose')) {
            return false;
        }

        $type = (string) $response->headers->get('content-type', '');
        if (! str_contains($type, 'text/html')) {
            return false;
        }

        $agent = (string) $request->userAgent();
        if ($agent !== '' && preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram/i', $agent)) {
            return false;
        }

        try {
            return Schema::hasTable('page_views') && $request->hasSession();
        } catch (\Throwable) {
            return false;
        }
    }
}
