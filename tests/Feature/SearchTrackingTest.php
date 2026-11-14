<?php

namespace Tests\Feature;

use App\Models\SearchQuery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function beacon(array $payload, array $headers = [])
    {
        return $this->call('POST', '/search/track', [], [], [], array_merge([
            'CONTENT_TYPE' => 'text/plain',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (test)',
        ], $headers), json_encode($payload));
    }

    public function test_palette_beacon_records_a_search_and_a_click(): void
    {
        $this->beacon([
            'query' => '  Mobile   Money ',
            'locale' => 'fr',
            'source' => 'palette',
            'results_count' => 7,
            'clicked_id' => 'faq:general-1',
            'clicked_type' => 'faq',
            'clicked_position' => 1,
        ])->assertNoContent();

        $this->assertDatabaseCount('search_queries', 1);
        $this->assertDatabaseHas('search_queries', [
            'query' => 'Mobile Money',
            'normalized' => 'mobile money',
            'locale' => 'fr',
            'source' => 'palette',
            'results_count' => 7,
            'clicked_id' => 'faq:general-1',
            'clicked_type' => 'faq',
            'clicked_position' => 1,
            'is_bot' => false,
        ]);

        $row = SearchQuery::first();
        $this->assertNotNull($row->visitor_id);
        $this->assertNull($row->getAttribute('ip'));
    }

    public function test_invalid_beacons_are_rejected(): void
    {
        $this->call('POST', '/search/track', [], [], [], ['CONTENT_TYPE' => 'text/plain'], 'not json')->assertStatus(400);
        $this->beacon(['query' => 'a', 'locale' => 'fr'])->assertStatus(422);
        $this->beacon(['query' => 'loyer', 'locale' => 'xx'])->assertStatus(422);
        $this->beacon(['query' => str_repeat('a', 200), 'locale' => 'fr'])->assertStatus(422);
        $this->beacon(['locale' => 'fr'])->assertStatus(422);

        $this->assertDatabaseCount('search_queries', 0);
    }

    public function test_results_page_counts_a_search_once(): void
    {
        $this->get('/recherche?q=loyer')->assertOk();
        $this->assertDatabaseCount('search_queries', 1);

        $row = SearchQuery::first();
        $this->assertSame('page', $row->source);
        $this->assertSame(32, strlen($row->token));
        $this->assertGreaterThan(0, $row->results_count);

        // Filtres et retour gardent le jeton : pas de nouvelle ligne.
        $this->get('/recherche?q=loyer&type=faq&t='.$row->token)->assertOk();
        $this->get('/recherche?q=loyer&t='.$row->token)->assertOk();
        $this->assertDatabaseCount('search_queries', 1);

        // Requête vide ou trop courte : rien.
        $this->get('/recherche')->assertOk();
        $this->get('/recherche?q=a')->assertOk();
        $this->assertDatabaseCount('search_queries', 1);

        // Sans résultat : compté avec 0.
        $this->get('/recherche?q=zzzzzzqq')->assertOk();
        $this->assertDatabaseHas('search_queries', ['query' => 'zzzzzzqq', 'results_count' => 0]);
    }

    public function test_click_from_results_page_completes_the_row_once_within_an_hour(): void
    {
        $this->get('/recherche?q=starter')->assertOk();
        $row = SearchQuery::first();

        $this->get('/search/go?t='.$row->token.'&id=plan:starter&p=2&l=fr')->assertRedirect('http://localhost/tarifs#starter');
        $row->refresh();
        $this->assertSame('plan:starter', $row->clicked_id);
        $this->assertSame('plan', $row->clicked_type);
        $this->assertSame(2, $row->clicked_position);

        // Un second clic ne remplace pas le premier.
        $this->get('/search/go?t='.$row->token.'&id=plan:premium&p=3&l=fr')->assertRedirect('http://localhost/tarifs#premium');
        $this->assertSame('plan:starter', $row->fresh()->clicked_id);

        // Jeton trop ancien : la redirection fonctionne, rien n'est écrit.
        $old = SearchQuery::create([
            'query' => 'premium', 'normalized' => 'premium', 'locale' => 'fr', 'source' => 'page',
            'token' => str_repeat('b', 32), 'results_count' => 1, 'searched_at' => now()->subHours(2),
        ]);
        $this->get('/search/go?t='.$old->token.'&id=plan:premium&p=1&l=fr')->assertRedirect('http://localhost/tarifs#premium');
        $this->assertNull($old->fresh()->clicked_id);

        // Jeton inconnu : redirection seule.
        $this->get('/search/go?t='.str_repeat('c', 32).'&id=plan:premium&p=1&l=en')->assertRedirect('http://localhost/en/tarifs#premium');
    }

    public function test_bots_are_flagged_and_excluded_from_the_report(): void
    {
        $this->beacon(['query' => 'robot', 'locale' => 'fr', 'results_count' => 0], ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1)'])->assertNoContent();
        $this->beacon(['query' => 'humain', 'locale' => 'fr', 'results_count' => 0])->assertNoContent();

        $this->assertDatabaseHas('search_queries', ['query' => 'robot', 'is_bot' => true]);
        $this->assertDatabaseHas('search_queries', ['query' => 'humain', 'is_bot' => false]);

        $this->actingAs(User::factory()->create())
            ->get('/admin/recherches')
            ->assertOk()
            ->assertSee('humain', false)
            ->assertDontSee('>robot<', false);
    }

    public function test_report_shows_totals_missing_queries_and_clicked_results(): void
    {
        $this->beacon(['query' => 'Loyer', 'locale' => 'fr', 'results_count' => 5, 'clicked_id' => 'feature:calendar-check', 'clicked_type' => 'feature', 'clicked_position' => 2]);
        $this->beacon(['query' => 'loyer', 'locale' => 'fr', 'results_count' => 5]);
        $this->beacon(['query' => 'caution solidaire', 'locale' => 'fr', 'results_count' => 0]);
        $this->get('/en/search?q=pricing');

        $response = $this->actingAs(User::factory()->create())->get('/admin/recherches?period=7');

        $response->assertOk()
            ->assertSee('Recherches', false)
            ->assertSee('Requêtes sans résultat', false)
            ->assertSee('caution solidaire', false)
            ->assertSee('calendar-check', false)
            ->assertSee('Fonctionnalités', false)
            ->assertSee('recherche?q=caution%20solidaire', false)
            ->assertSee('25 %', false);

        // « Loyer » et « loyer » sont regroupés.
        $this->assertSame(1, substr_count((string) $response->getContent(), '>Loyer<'));
        $this->assertSame(0, substr_count((string) $response->getContent(), '>loyer<'));

        $this->actingAs(User::factory()->create())->get('/admin/recherches?locale=en&source=page')
            ->assertOk()->assertSee('pricing', false)->assertDontSee('caution solidaire', false);
    }

    public function test_report_requires_an_administrator(): void
    {
        $this->get('/admin/recherches')->assertRedirect('/admin/login');
    }

    public function test_old_rows_are_pruned(): void
    {
        SearchQuery::create(['query' => 'vieux', 'normalized' => 'vieux', 'locale' => 'fr', 'source' => 'page', 'results_count' => 1, 'searched_at' => now()->subMonths(14)]);
        SearchQuery::create(['query' => 'recent', 'normalized' => 'recent', 'locale' => 'fr', 'source' => 'page', 'results_count' => 1, 'searched_at' => now()->subMonths(2)]);

        $this->artisan('model:prune', ['--model' => [SearchQuery::class]])->assertSuccessful();

        $this->assertDatabaseMissing('search_queries', ['query' => 'vieux']);
        $this->assertDatabaseHas('search_queries', ['query' => 'recent']);
    }
}
