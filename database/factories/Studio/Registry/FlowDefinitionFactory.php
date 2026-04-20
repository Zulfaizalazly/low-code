<?php

namespace Database\Factories\Studio\Registry;

use App\Studio\Registry\FlowDefinition;
use App\Studio\Registry\FeatureVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlowDefinitionFactory extends Factory
{
    protected $model = FlowDefinition::class;

    public function definition(): array
    {
        return [
            'feature_version_id' => FeatureVersion::factory(),
            'key' => $this->faker->unique()->slug(2),
            'name' => $this->faker->words(3, true),
            'trigger_type' => $this->faker->randomElement(['manual_entry', 'domain_event', 'schedule', 'api_trigger']),
            'trigger_config' => null,
            'entry_mode' => 'user_launch',
            'is_primary' => false,
        ];
    }

    public function manualEntry(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type' => 'manual_entry',
            'entry_mode' => 'user_launch',
        ]);
    }

    public function domainEvent(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type' => 'domain_event',
            'entry_mode' => 'event_driven',
        ]);
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }
}
