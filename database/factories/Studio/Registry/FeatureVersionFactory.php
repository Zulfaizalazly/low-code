<?php

namespace Database\Factories\Studio\Registry;

use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureVersionFactory extends Factory
{
    protected $model = FeatureVersion::class;

    public function definition(): array
    {
        return [
            'feature_id' => Feature::factory(),
            'version_no' => 1,
            'status' => 'draft',
            'change_summary' => $this->faker->sentence(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }
}
