<?php

namespace Database\Factories\Studio\Registry;

use App\Studio\Registry\PageDefinition;
use App\Studio\Registry\FeatureVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageDefinitionFactory extends Factory
{
    protected $model = PageDefinition::class;

    public function definition(): array
    {
        return [
            'feature_version_id' => FeatureVersion::factory(),
            'key' => $this->faker->unique()->slug(2),
            'name' => $this->faker->words(3, true),
            'page_type' => $this->faker->randomElement(['workflow_form', 'dashboard', 'detail_view', 'listing', 'approval_view']),
            'layout_type' => 'single_column',
            'route_key' => null,
            'is_entry_page' => false,
            'config' => null,
        ];
    }

    public function workflowForm(): static
    {
        return $this->state(fn (array $attributes) => [
            'page_type' => 'workflow_form',
        ]);
    }

    public function dashboard(): static
    {
        return $this->state(fn (array $attributes) => [
            'page_type' => 'dashboard',
            'layout_type' => 'grid',
        ]);
    }

    public function entryPage(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_entry_page' => true,
        ]);
    }
}
