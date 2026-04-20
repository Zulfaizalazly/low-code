<?php

namespace Database\Factories\Studio\Registry;

use App\Studio\Registry\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(2),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'domain' => $this->faker->randomElement(['facility', 'customer', 'payment', 'reporting']),
            'icon' => $this->faker->randomElement(['📋', '💰', '👤', '📊']),
            'status' => 'draft',
            'scope_level' => 'platform',
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    public function facility(): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => 'facility',
        ]);
    }
}
