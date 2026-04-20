<?php

namespace Tests\Feature\Studio;

use App\Models\User;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Registry\PageDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBuilderApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected FeatureVersion $version;
    protected PageDefinition $page;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
        
        $this->user = User::factory()->create();
        $this->user->assignRole('super-admin');
        
        $feature = Feature::factory()->create();
        $this->version = FeatureVersion::factory()->create([
            'feature_id' => $feature->id,
            'status' => 'draft',
        ]);
        
        $this->page = PageDefinition::factory()->create([
            'feature_version_id' => $this->version->id,
        ]);
    }

    public function test_can_save_page_via_api()
    {
        $pageData = [
            'steps' => [
                [
                    'step_key' => 'step1',
                    'title' => 'Customer Info',
                    'sort_order' => 1,
                    'fields' => [
                        [
                            'field_key' => 'name',
                            'label' => 'Full Name',
                            'component_type' => 'input_text',
                            'is_required' => true,
                            'sort_order' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/studio/pages/{$this->page->id}/save", $pageData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Page saved successfully',
            ]);

        $this->assertDatabaseHas('form_steps', [
            'page_definition_id' => $this->page->id,
            'step_key' => 'step1',
        ]);
    }

    public function test_can_validate_page_via_api()
    {
        // Create a valid page
        $step = $this->page->steps()->create([
            'step_key' => 'step1',
            'title' => 'Customer Info',
            'sort_order' => 1,
        ]);

        $step->fields()->create([
            'field_key' => 'name',
            'label' => 'Full Name',
            'component_type' => 'input_text',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/studio/pages/{$this->page->id}/validate");

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
            ]);
    }

    public function test_validation_fails_for_invalid_page()
    {
        // Create page without steps
        $response = $this->actingAs($this->user)
            ->postJson("/api/studio/pages/{$this->page->id}/validate");

        $response->assertStatus(200)
            ->assertJson([
                'valid' => false,
            ])
            ->assertJsonStructure([
                'valid',
                'errors',
            ]);
    }

    public function test_can_get_entities_list()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/studio/pages/entities');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'entities',
            ]);
    }

    public function test_validation_detects_duplicate_field_keys()
    {
        $step = $this->page->steps()->create([
            'step_key' => 'step1',
            'title' => 'Customer Info',
            'sort_order' => 1,
        ]);

        // Create two fields with same key
        $step->fields()->create([
            'field_key' => 'name',
            'label' => 'Full Name',
            'component_type' => 'input_text',
            'sort_order' => 1,
        ]);

        $step->fields()->create([
            'field_key' => 'name', // Duplicate!
            'label' => 'Name Again',
            'component_type' => 'input_text',
            'sort_order' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/studio/pages/{$this->page->id}/validate");

        $response->assertStatus(200)
            ->assertJson([
                'valid' => false,
            ]);

        $errors = $response->json('errors');
        $this->assertStringContainsString('duplicate', strtolower(json_encode($errors)));
    }

    public function test_unauthorized_user_cannot_save_page()
    {
        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->assignRole('business-user');

        $response = $this->actingAs($unauthorizedUser)
            ->postJson("/api/studio/pages/{$this->page->id}/save", [
                'steps' => [],
            ]);

        $response->assertStatus(403);
    }

    public function test_can_save_page_with_bindings()
    {
        $pageData = [
            'steps' => [
                [
                    'step_key' => 'step1',
                    'title' => 'Customer Info',
                    'sort_order' => 1,
                    'fields' => [
                        [
                            'field_key' => 'name',
                            'label' => 'Full Name',
                            'component_type' => 'input_text',
                            'is_required' => true,
                            'sort_order' => 1,
                        ],
                    ],
                ],
            ],
            'bindings' => [
                [
                    'field_key' => 'name',
                    'binding_mode' => 'direct',
                    'entity_name' => 'Customer',
                    'entity_path' => 'name',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/studio/pages/{$this->page->id}/save", $pageData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('field_bindings', [
            'page_definition_id' => $this->page->id,
            'field_key' => 'name',
            'entity_name' => 'Customer',
        ]);
    }
}
