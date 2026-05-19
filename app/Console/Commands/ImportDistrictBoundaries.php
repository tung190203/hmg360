<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\District;
use Illuminate\Support\Facades\File;

class ImportDistrictBoundaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-district-boundaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import district boundaries from boundaries.js into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = public_path('js/boundaries.js');

        if (!File::exists($filePath)) {
            $this->error("File boundaries.js not found at: {$filePath}");
            return;
        }

        $content = File::get($filePath);

        // Regular expression to find the content of the boundaries object
        // It looks for "let boundaries = {" and matches until the final "};"
        if (preg_match('/let boundaries\s*=\s*(\{[\s\S]*\});?/', $content, $matches)) {
            $jsonStr = $matches[1];
            
            // Clean up the string to be valid JSON if necessary
            // In the file it seems it might be JS object notation (unquoted keys, etc.)
            // But looking at the content, it has quoted keys: "Xã Thuận An": [[...]]
            // However, it might have trailing commas which PHP's json_decode doesn't like.
            
            // Basic cleanup for trailng commas in arrays/objects for json_decode
            $jsonStr = preg_replace('/,\s*([\]\}])/', '$1', $jsonStr);
            
            $boundaries = json_decode($jsonStr, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("Failed to parse boundaries JSON: " . json_last_error_msg());
                return;
            }

            $this->info("Boundaries type: " . gettype($boundaries));
            $this->info("Found " . count($boundaries) . " boundaries in file.");
            $sampleKey = array_key_first($boundaries);
            $this->info("Sample key: '{$sampleKey}'");
            $this->info("Sample key hex: " . bin2hex($sampleKey));
            
            $count = 0;
            foreach ($boundaries as $name => $coords) {
                $trimmedName = trim($name);
                
                // Search in translatable 'name' field (JSON column)
                // We check both 'vi' and 'en' locales just in case
                $district = District::where('name->vi', $trimmedName)
                    ->orWhere('name->en', $trimmedName)
                    ->first();

                if ($district) {
                    // district->boundary is cast to array in the model
                    $district->boundary = $coords;
                    $district->save();
                    $this->info("Updated boundary for: {$trimmedName}");
                    $count++;
                } else {
                    $this->warn("District not found: '{$name}'");
                }
            }

            $this->info("Successfully updated {$count} districts.");
        } else {
            $this->error("Could not find boundaries object in boundaries.js");
        }
    }
}
