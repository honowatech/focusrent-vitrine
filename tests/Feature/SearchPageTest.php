<?php

namespace Tests\Feature;

use App\Support\SearchEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<string>
     */
    protected function ids(string $locale, string $query, int $take = 3): array
    {
        return array_map(
            fn (array $result) => $result['document']['id'],
            array_slice(SearchEngine::for($locale)->search($query), 0, $take)
        );
    }

    public function test_engine_finds_the_expected_documents(): void
    {
        // Mot simple, expression, accent, faute, synonyme, anglais.
        $this->assertContains('feature:calendar-check', $this->ids('fr', 'relance'));
        $this->assertSame('faq:general-1', $this->ids('fr', 'mobile money')[0]);
        $this->assertSame($this->ids('fr', 'démo'), $this->ids('fr', 'demo'));
        $this->assertSame('feature:calendar-check', $this->ids('fr', 'relanse')[0]);
        $this->assertSame('faq:general-1', $this->ids('fr', 'momo')[0]);
        $this->assertContains('plan:business', $this->ids('fr', 'logement', 4));
        $this->assertSame('feature:university', $this->ids('en', 'lease')[0]);
        $this->assertStringStartsWith('page:pricing', $this->ids('en', 'pricing')[0]);
        $this->assertSame('feature:chart-line', $this->ids('fr', 'tableau de bord')[0]);
    }

    public function test_plural_and_singular_match_the_same_documents(): void
    {
        $this->assertSame($this->ids('fr', 'contrat', 5), $this->ids('fr', 'contrats', 5));
    }

    public function test_a_known_word_ranks_its_extensions_behind(): void
    {
        $results = SearchEngine::for('fr')->search('demo');
        $ids = array_column(array_column($results, 'document'), 'id');

        // « démo » existe tel quel : la FAQ « La démo est-elle payante ? » passe devant.
        $this->assertSame('faq:pricing-4', $ids[0]);
    }

    public function test_all_words_first_then_at_least_one(): void
    {
        $this->assertNotEmpty($this->ids('fr', 'mobile zzzzzz'));
        $this->assertSame([], $this->ids('fr', 'zzzzzz'));
        $this->assertSame([], $this->ids('fr', 'a'));
    }

    public function test_highlight_escapes_html_before_marking(): void
    {
        $html = SearchEngine::highlight('Payer via <b>Orange</b> Money & Démo', ['orange', 'demo']);

        $this->assertSame('Payer via &lt;b&gt;<mark>Orange</mark>&lt;/b&gt; Money &amp; <mark>Démo</mark>', $html);
        $this->assertSame('Rien', SearchEngine::highlight('Rien', []));
    }

    public function test_results_page_is_grouped_filterable_and_noindex(): void
    {
        $response = $this->get('/recherche?q=mobile+money');

        $response->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, follow')
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('résultats pour « mobile money »', false)
            ->assertSee('id="group-faq"', false)
            ->assertSee('id="group-page"', false)
            ->assertSee('<mark>Mobile</mark>', false)
            ->assertSee('/search/go?', false)
            ->assertSee('type=faq', false);

        $filtered = $this->get('/recherche?q=mobile+money&type=faq');
        $filtered->assertOk()
            ->assertSee('id="group-faq"', false)
            ->assertDontSee('id="group-page"', false)
            ->assertSee('aria-current="true"', false);

        $english = $this->get('/en/search?q=lease');
        $english->assertOk()
            ->assertSee('<html lang="en"', false)
            ->assertSee('results for “lease”', false)
            ->assertSee('id="group-feature"', false);
    }

    public function test_results_page_states(): void
    {
        $this->get('/recherche')->assertOk()
            ->assertSee('Que cherchez-vous ?', false)
            ->assertSee('autofocus', false);

        $this->get('/recherche?q=a')->assertOk()
            ->assertSee('Saisissez au moins 2 caractères', false);

        $this->get('/recherche?q=zzzzzzqq')->assertOk()
            ->assertSee('Rien ne correspond à « zzzzzzqq »', false)
            ->assertSee('href="http://localhost/contact"', false)
            ->assertSee('href="http://localhost/faq"', false);
    }

    public function test_query_is_escaped_and_bounded(): void
    {
        $response = $this->get('/recherche?q='.rawurlencode('<script>alert(1)</script>'));

        $response->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);

        $long = str_repeat('a', 200);
        $this->get('/recherche?q='.$long)->assertOk()->assertDontSee($long, false);
    }

    public function test_result_links_work_without_javascript(): void
    {
        $page = $this->get('/recherche?q=starter');
        $page->assertOk();

        preg_match('/href="([^"]*search\/go[^"]*)"/', (string) $page->getContent(), $match);
        $this->assertNotEmpty($match, 'Aucun lien de résultat');

        $this->get(html_entity_decode($match[1]))->assertRedirect('http://localhost/tarifs#starter');

        $this->get('/search/go?id=unknown:doc&l=fr')->assertRedirect('http://localhost/recherche');
    }

    public function test_language_switcher_keeps_the_query(): void
    {
        $this->get('/recherche?q=loyer&type=faq')->assertOk()
            ->assertSee('href="http://localhost/en/search?q=loyer&amp;type=faq"', false);

        $this->get('/en/search?q=rent')->assertOk()
            ->assertSee('href="http://localhost/recherche?q=rent"', false);
    }

    public function test_search_trigger_is_a_link_to_the_results_page(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<a href="http://localhost/recherche" class="search-trigger" data-search-open', false)
            ->assertSee('class="search-drawer-field" data-search-open', false)
            ->assertSee('role="combobox"', false)
            ->assertSee('enterkeyhint="search"', false);
    }
}
