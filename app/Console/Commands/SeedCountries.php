<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Nation;

class SeedCountries extends Command
{
    protected $signature = 'nations:import';
    protected $description = 'Import countries from Wikidata into nations table';

    public function handle()
    {
        $this->info("Fetching countries from Wikidata...");

        $sparql = <<<SPARQL
        SELECT ?iso2 ?iso3 ?countryLabel WHERE {
          ?country wdt:P297 ?iso2.
          OPTIONAL { ?country wdt:P298 ?iso3. }
          SERVICE wikibase:label { bd:serviceParam wikibase:language "vi,en". }
        }
        SPARQL;        

        $url = "https://query.wikidata.org/sparql";
        $response = Http::withHeaders([
            "Accept" => "application/sparql-results+json"
        ])->get($url, [
            'query' => $sparql
        ]);

        if (!$response->successful()) {
            $this->error("Failed to fetch data from Wikidata");
            return;
        }

        $data = $response->json();
        $results = $data['results']['bindings'] ?? [];

        $count = 0;

        foreach ($results as $row) {
            $iso2  = $row['iso2']['value'] ?? null;
            $iso3  = $row['iso3']['value'] ?? null;
            $name  = $row['countryLabel']['value'] ?? null;

            if (!$name) {
                continue;
            }

            Nation::updateOrCreate(
                ['iso_code' => $iso2],
                [
                    'name' => $name,
                    'iso_code' => $iso2 ?? $iso3
                ]
            );

            $count++;
        }

        $this->info("Imported/updated {$count} nations.");
    }
}
