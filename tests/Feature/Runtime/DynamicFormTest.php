<?php

namespace Tests\Feature\Runtime;

use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Registry\PageDefinition;
use App\Studio\Registry\FormStep;
use App\Studio\Registry\FormField;
use App\Studio\Registry\FieldBinding;
use App\Studio\Registry\FeatureMenuItem;
use App\Livewire\Runtime\FormEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Models\User;

class DynamicFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'HQ Admin',
            'email' => 'hq@example.com',
            'password' => 'password',
            'role' => 'hq_admin',
            'entity_id' => 1,
            'branch_id' => 1,
        ]);

        $this->actingAs($this->user);
    }

    public function test_can_render_dynamic_form_from_registry()
    {
        // 1. Setup Registry Data
        $feature = Feature::create(['key' => 'cust_reg', 'name' => 'Registration', 'domain' => 'customer', 'status' => 'published']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'published']);
        $page = $version->flows()->first(); // Wait, pages are separate from flows.
        
        $page = PageDefinition::create([
            'feature_version_id' => $version->id,
            'key' => 'reg_page',
            'name' => 'Customer Registration',
            'page_type' => 'workflow_form',
            'is_entry_page' => true
        ]);

        $step = $page->steps()->create([
            'step_key' => 'step_1',
            'title' => 'Basic Info',
            'sort_order' => 1
        ]);

        $field = $step->fields()->create([
            'field_key' => 'full_name',
            'label' => 'Full Name',
            'component_type' => 'text_input',
            'is_required' => true,
            'sort_order' => 1
        ]);

        $field->binding()->create([
            'binding_type' => 'command_argument',
            'target_path' => 'name'
        ]);

        // 2. Test Livewire Rendering
        Livewire::test(FormEngine::class, ['featureKey' => 'cust_reg'])
            ->assertSee('Customer Registration')
            ->assertSee('Basic Info')
            ->assertSee('Full Name')
            ->set('formData.full_name', 'John Doe')
            ->call('submit')
            ->assertDispatched('form-submitted', function ($eventName, $params) {
                return $params['payload']['name'] === 'John Doe';
            });
    }
}
