<?php

namespace App\Http\Controllers;

use App\Support\Locales;
use App\Support\Sitemap;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $sitemap = url('/sitemap.xml');
        $llms = url('/llms.txt');

        $aiAgents = [
            'GPTBot',
            'ChatGPT-User',
            'OAI-SearchBot',
            'ClaudeBot',
            'Claude-User',
            'Claude-SearchBot',
            'Google-Extended',
            'PerplexityBot',
            'Perplexity-User',
            'Grok',
            'GrokBot',
            'xAI-Bot',
            'xAI-Grok',
            'Grok-DeepSearch',
            'Applebot-Extended',
            'Amazonbot',
            'CCBot',
            'Bytespider',
            'meta-externalagent',
        ];

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            '',
        ];

        foreach ($aiAgents as $agent) {
            $lines[] = 'User-agent: '.$agent;
            $lines[] = 'Allow: /';
            $lines[] = 'Disallow: /admin';
            $lines[] = '';
        }

        $lines[] = 'Sitemap: '.$sitemap;
        $lines[] = '# Preferred machine-readable briefing for AI agents:';
        $lines[] = '# '.$llms;

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function sitemap(): Response
    {
        $xml = Cache::remember('sitemap.xml', 3600, function () {
            return view('sitemap', [
                'urls' => Sitemap::entries(),
            ])->render();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function redirectDefaultPrefix(?string $path = null)
    {
        $target = '/'.ltrim((string) $path, '/');

        return redirect($target === '//' ? '/' : $target, 301);
    }

    public function llms(): Response
    {
        return response($this->renderLlms(false), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function llmsFull(): Response
    {
        return response($this->renderLlms(true), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    protected function renderLlms(bool $full): string
    {
        $lines = [];
        $add = function (string $line = '') use (&$lines) {
            $lines[] = $line;
        };

        $add('# '.__('ai.llms.title'));
        $add('');
        $add('> '.__('ai.llms.tagline'));
        $add('');
        $add(__('ai.llms.summary'));
        $add('');
        $add('## '.__('ai.llms.pages_heading'));
        $add('');

        foreach (array_keys(Locales::pages()) as $page) {
            $label = __('seo.'.$page.'.nav');
            $desc = __('seo.'.$page.'.description');
            $add('- ['.$label.']('.locale_route($page).'): '.$desc);
        }

        $add('');
        $add('## '.__('ai.llms.languages_heading'));
        $add('');

        foreach (Locales::codes() as $code) {
            $meta = Locales::available()[$code];
            $add('- ['.$meta['native'].']('.locale_route('llms', [], $code).') (`'.$code.'`)');
        }

        $add('');
        $add('## '.__('ai.llms.agent_heading'));
        $add('');
        $add('- ['.__('ai.llms.full_label').']('.locale_route('llms-full').')');
        $add('- ['.__('ai.llms.contact_label').']('.locale_route('contact').')');
        $add('- Demo: https://demomeuble.focusrent.cm');

        if (! $full) {
            $add('');
            $add('## '.__('ai.llms.facts_heading'));
            $add('');
            foreach (__('ai.llms.facts') as $fact) {
                $add('- '.$fact);
            }

            return implode("\n", $lines)."\n";
        }

        $add('');
        $add('## '.__('ai.full.product_heading'));
        $add('');
        $add(__('ai.full.product'));
        $add('');
        $add('## '.__('ai.full.audience_heading'));
        $add('');
        $add(__('ai.full.audience'));
        $add('');
        $add('## '.__('home.features.title'));
        $add('');
        foreach (__('home.features.items') as $item) {
            $add('- **'.$item['title'].'**: '.$item['text']);
        }
        $add('');
        $add('## '.__('pricing.title'));
        $add('');
        $add(__('pricing.lead'));
        $add('');
        $add(__('pricing.compare_caption'));
        $add('URL: '.locale_route('pricing'));
        $add('');
        foreach (__('home.pricing.plans') as $plan) {
            $add('- **'.$plan['name'].'**: '.$plan['price'].' '.__('home.pricing.currency').' '.__('home.pricing.period').' — '.$plan['units'].'; '.$plan['users'].'; '.implode('; ', $plan['features']));
        }
        $add('');
        $add(__('home.pricing.note'));
        $add('');
        foreach (__('pricing.billing') as $line) {
            $add('- '.$line);
        }
        $add('');
        $add('## '.__('pricing.faq_title'));
        $add('');
        foreach (__('pricing.faq') as $item) {
            $add('### '.$item['q']);
            $add($item['a']);
            $add('');
        }
        $add('## '.__('faq.title'));
        $add('');
        foreach (__('faq.items') as $item) {
            $add('### '.$item['q']);
            $add($item['a']);
            $add('');
        }
        $add('## '.__('ai.full.contact_heading'));
        $add('');
        foreach (__('ai.full.contact') as $line) {
            $add('- '.$line);
        }
        $add('');
        $add('## '.__('ai.full.company_heading'));
        $add('');
        $add(__('ai.full.company'));
        $add('');

        return implode("\n", $lines);
    }
}
