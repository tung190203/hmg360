<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class UpdateVrtourCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            $slug = $project->slug ?: Str::slug($project->name);
            $vrtourCode = 'vrtour-' . $slug;

            $project->update([
                'vrtour_code' => $vrtourCode
            ]);
        }
    }
}
