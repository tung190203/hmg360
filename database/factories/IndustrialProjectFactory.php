<?php

namespace Database\Factories;

use App\Models\ProductType;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IndustrialProject>
 */
class IndustrialProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $projectId = Project::all()->pluck('id')->random();
        if (!$projectId) {
            // If no projects exist, create a new one
            $project = Project::factory()->create();
            $projectId = $project->id;
        }
        $productType = ProductType::all()->pluck('id')->random();
        if (!$productType) {
            // If no product types exist, create a new one
            $productType = ProductType::factory()->create()->id;
        }
        return [
            'project_id' => $projectId,
            'name' => $this->faker->company . ' Industrial Project',
            'link' => $this->faker->url,
            'code' => $this->faker->unique()->bothify('IP-###??'), // Unique code for the industrial project
            'acreage' => $this->faker->randomFloat(2, 1, 1000), // Acreage in hectares
            'description' => $this->faker->paragraph,
            'product_type' => $productType,
            'price' => $this->faker->numberBetween(1000000, 1000000000),
        ];
    }
}
