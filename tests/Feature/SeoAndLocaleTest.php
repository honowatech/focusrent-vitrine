<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_french_home_has_seo_and_hreflang(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<html lang="fr"', false);
        $response->assertSee('hreflang="en"', false);
        $response->assertSee('hreflang="x-default"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('La gestion locative', false);
        $response->assertHeader('Content-Language', 'fr');
    }

    public function test_english_home_is_translated(): void
    {
        $response = $this->get('/en');

        $response->assertOk();
        $response->assertSee('<html lang="en"', false);
        $response->assertSee('Rental management', false);
        $response->assertSee('See pricing', false);
        $response->assertHeader('Content-Language', 'en');
    }

    public function test_english_inner_pages(): void
    {
        $this->get('/en/about')->assertOk()->assertSee('Our story', false);
        $this->get('/en/tarifs')->assertOk()->assertSee('How much does Focus Rent cost?', false);
        $this->get('/en/faq')->assertOk()->assertSee('Mobile Money', false);
        $this->get('/en/contact')->assertOk()->assertSee('free demo', false);
        $this->get('/en/privacy')->assertOk()->assertSee('Privacy policy', false);
        $this->get('/en/terms')->assertOk()->assertSee('Terms of use', false);
    }

    public function test_default_locale_prefix_redirects(): void
    {
        $this->get('/fr')->assertRedirect('/');
        $this->get('/fr/about')->assertRedirect('/about');
        $this->get('/fr/tarifs')->assertRedirect('/tarifs');
    }

    public function test_pricing_page_is_indexable_in_both_languages(): void
    {
        $french = $this->get('/tarifs');
        $french->assertOk();
        $french->assertSee('<h1', false);
        $french->assertSee('Tarifs Focus Rent', false);
        $french->assertSee('10 000', false);
        $french->assertSee('59 000', false);
        $french->assertSee('hreflang="en"', false);
        $french->assertSee('hreflang="x-default"', false);
        $french->assertSee('rel="canonical"', false);
        $french->assertSee('OfferCatalog', false);
        $french->assertSee('UnitPriceSpecification', false);
        $french->assertSee('"price":"10000"', false);
        $french->assertSee('FAQPage', false);
        $french->assertSee('aria-current="page"', false);
        $french->assertHeader('Content-Language', 'fr');

        $english = $this->get('/en/tarifs');
        $english->assertOk();
        $english->assertSee('Focus Rent pricing', false);
        $english->assertSee('10,000', false);
        $english->assertSee('before tax', false);
        $english->assertSee('<html lang="en"', false);
        $english->assertHeader('Content-Language', 'en');

        $this->get('/pricing')->assertStatus(301)->assertRedirect('/tarifs');
        $this->get('/en/pricing')->assertStatus(301)->assertRedirect('/en/tarifs');

        $this->get('/')->assertOk()->assertSee('/tarifs', false);
        $this->get('/llms.txt')->assertOk()->assertSee('/tarifs', false);
        $this->get('/llms-full.txt')->assertOk()->assertSee('Quel est le prix de Focus Rent ?', false);
        $this->get('/en/llms-full.txt')->assertOk()->assertSee('How much does Focus Rent cost?', false);

        $sitemap = $this->get('/sitemap.xml');
        $sitemap->assertOk();
        $sitemap->assertSee('/tarifs', false);
        $sitemap->assertSee('/en/tarifs', false);
        $sitemap->assertSee('<priority>0.9</priority>', false);
    }

    public function test_robots_allows_ai_crawlers(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('User-agent: Grok', false);
        $response->assertSee('User-agent: GPTBot', false);
        $response->assertSee('Sitemap:', false);
        $response->assertSee('Allow: /', false);
    }

    public function test_sitemap_lists_both_locales(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('hreflang="fr"', false);
        $response->assertSee('hreflang="en"', false);
        $response->assertSee('hreflang="x-default"', false);
        $response->assertSee('/en/about', false);
        $response->assertSee('/faq', false);
        $response->assertSee('<lastmod>', false);
        $response->assertSee('image:loc', false);

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $this->assertGreaterThanOrEqual(12, count($xml->url));
    }

    public function test_llms_txt_is_machine_readable(): void
    {
        $this->get('/llms.txt')->assertOk()->assertSee('Focus Rent', false);
        $this->get('/en/llms-full.txt')->assertOk()->assertSee('10,000 XAF', false);
        $this->get('/llms-full.txt')->assertOk()->assertSee('10 000 FCFA', false);
    }
}
