<?php

namespace App\Console\Commands;

use App\Support\Locales;
use App\Support\SearchIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BuildSearchIndex extends Command
{
    protected $signature = 'search:index {--locale=* : Langue(s) à générer, toutes par défaut}';

    protected $description = 'Génère l’index JSON statique de la recherche (public/search/{locale}.json)';

    public function handle(): int
    {
        $locales = array_filter((array) $this->option('locale'));
        if ($locales === []) {
            $locales = Locales::codes();
        }

        File::ensureDirectoryExists(public_path('search'));

        foreach ($locales as $locale) {
            if (! Locales::isSupported($locale)) {
                $this->error("Langue inconnue : {$locale}");

                return self::FAILURE;
            }

            $index = SearchIndex::build($locale);
            $json = json_encode($index, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($json === false) {
                $this->error("Impossible d’encoder l’index {$locale}.");

                return self::FAILURE;
            }

            File::put(SearchIndex::path($locale), $json);

            $counts = [];
            foreach ($index['documents'] as $document) {
                $counts[$document['type']] = ($counts[$document['type']] ?? 0) + 1;
            }
            ksort($counts);

            $summary = implode(', ', array_map(fn ($type, $count) => "{$type} {$count}", array_keys($counts), $counts));
            $size = round(strlen($json) / 1024, 1);

            $this->info(sprintf('%s : %d documents (%s), %s Ko → public/search/%s.json', $locale, count($index['documents']), $summary, $size, $locale));
        }

        return self::SUCCESS;
    }
}
