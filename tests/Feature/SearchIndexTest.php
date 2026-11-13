<?php

namespace Tests\Feature;

use App\Support\Locales;
use App\Support\SearchIndex;
use App\Support\SearchText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SearchIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_writes_one_index_per_locale(): void
    {
        $this->artisan('search:index')->assertSuccessful();

        foreach (Locales::codes() as $locale) {
            $path = SearchIndex::path($locale);
            $this->assertFileExists($path);

            $index = json_decode((string) File::get($path), true);
            $this->assertSame($locale, $index['locale']);
            $this->assertNotEmpty($index['documents']);
        }
    }

    public function test_every_type_is_present_in_every_locale(): void
    {
        foreach (Locales::codes() as $locale) {
            $types = array_count_values(array_column(SearchIndex::build($locale)['documents'], 'type'));

            foreach (config('search.groups') as $group) {
                $this->assertGreaterThan(0, $types[$group] ?? 0, "Type {$group} absent de l'index {$locale}");
            }
        }
    }

    public function test_documents_are_clean_and_unique(): void
    {
        foreach (Locales::codes() as $locale) {
            $documents = SearchIndex::build($locale)['documents'];
            $ids = array_column($documents, 'id');

            $this->assertSame(count($ids), count(array_unique($ids)), "Identifiants en double dans {$locale}");

            foreach ($documents as $document) {
                $this->assertMatchesRegularExpression('/^[a-z]+:[\w#.-]+$/u', $document['id']);
                $this->assertNotSame('', $document['label'], $document['id'].' sans libellé');
                $this->assertStringStartsWith('/', $document['url'], $document['id'].' : URL non relative');
                $this->assertLessThanOrEqual(SearchIndex::EXCERPT_LENGTH + 1, mb_strlen($document['excerpt']), $document['id'].' : extrait trop long');
                $this->assertLessThanOrEqual(SearchIndex::BODY_LENGTH + 1, mb_strlen($document['body']), $document['id'].' : corps trop long');

                foreach (['excerpt', 'body', 'label', 'title'] as $field) {
                    $this->assertStringNotContainsString('](', $document[$field], $document['id'].' : Markdown résiduel');
                    $this->assertStringNotContainsString('**', $document[$field], $document['id'].' : Markdown résiduel');
                    $this->assertStringNotContainsString('<', $document[$field], $document['id'].' : HTML résiduel');
                }
            }
        }
    }

    public function test_prices_only_live_in_plan_documents(): void
    {
        foreach (Locales::codes() as $locale) {
            foreach (SearchIndex::build($locale)['documents'] as $document) {
                if ($document['type'] === 'plan') {
                    $this->assertNotEmpty($document['price']);

                    continue;
                }

                foreach (['excerpt', 'body'] as $field) {
                    $this->assertFalse(SearchText::mentionsPrice($document[$field]), $document['id'].' affiche un prix hors fiche : '.$document[$field]);
                    $this->assertDoesNotMatchRegularExpression('/\b(10|28|59)[ ,\x{202f}\x{a0}]?000\b/u', $document[$field], $document['id'].' affiche un montant hors fiche');
                }
            }
        }
    }

    public function test_every_url_responds_and_every_anchor_exists(): void
    {
        $cache = [];

        foreach (Locales::codes() as $locale) {
            foreach (SearchIndex::build($locale)['documents'] as $document) {
                [$path, $anchor] = array_pad(explode('#', $document['url'], 2), 2, null);

                if (! isset($cache[$path])) {
                    $response = $this->get($path);
                    $response->assertOk();
                    $cache[$path] = $response->getContent();
                }

                if ($anchor !== null) {
                    $this->assertStringContainsString('id="'.$anchor.'"', $cache[$path], $document['id'].' : ancre #'.$anchor.' absente de '.$path);
                }
            }
        }
    }

    public function test_layout_points_to_the_index_of_the_current_language(): void
    {
        $this->artisan('search:index')->assertSuccessful();

        $this->get('/')->assertOk()
            ->assertSee('id="site-search"', false)
            ->assertSee('/search/fr.json?v=', false)
            ->assertSee('data-search-page="http://localhost/recherche"', false)
            ->assertSee('href="http://localhost/recherche"', false);

        $this->get('/en/about')->assertOk()
            ->assertSee('/search/en.json?v=', false)
            ->assertSee('data-search-page="http://localhost/en/search"', false);
    }

    public function test_search_page_stays_out_of_sitemap_and_llms(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/recherche', false)->assertDontSee('/en/search', false);
        $this->get('/llms.txt')->assertOk()->assertDontSee('/recherche', false);
    }
}
