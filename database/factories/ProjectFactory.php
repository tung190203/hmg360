<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'lat' => $this->faker->latitude(),
            'lng' => $this->faker->longitude(),
            'type_number' => \App\Models\ProjectType::factory(),
            'industry_number' => \App\Models\ProjectIndustries::factory(),
            'price' => $this->faker->numberBetween(1000000, 100000000),
            'link' => $this->faker->url(),
            'image' => $this->faker->imageUrl(640, 480, 'business'),
            'description' => $this->faker->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
