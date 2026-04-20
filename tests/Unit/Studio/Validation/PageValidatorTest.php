<?php

namespace Tests\Unit\Studio\Validation;

use Tests\TestCase;
use App\Studio\Validation\PageValidator;
use App\Studio\Registry\PageDefinition;
use App\Studio\Registry\FormStep;
use App\Studio\Registry\FormField;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PageValidatorTest extends TestCase
{
    use RefreshDatabase;

    protected PageValidator $validator;
    protected FeatureVersion $version;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new PageValidator();
        
        // Create feature version using factory
        $this->version = FeatureVersion::factory()->create();
    }

    public function test_it_fails_if_no_steps()
    {
        $page = PageDefinition::factory()->create([
            'feature_version_id' => $this->version->id,
        ]);
        
        $result = $this->validator->validate($page);
        
        $this->assertFalse($result['valid']);
        $this->assertContains("Page definition has no steps.", $result['errors']);
    }

    public function test_it_detects_duplicate_field_keys()
    {
        $page = PageDefinition::factory()->create([
            'feature_version_id' => $this->version->id,
        ]);
        $step = $page->steps()->create(['title' => 'Step 1', 'step_key' => 's1', 'sort_order' => 0]);
        
        $step->fields()->create([
            'field_key' => 'username',
            'label' => 'User',
            'component_type' => 'text_input',
            'sort_order' => 0
        ]);

        // Create a second step to avoid unique constraint
        $step2 = $page->steps()->create(['title' => 'Step 2', 'step_key' => 's2', 'sort_order' => 1]);
        
        $step2->fields()->create([
            'field_key' => 'username', // Same key as in step 1
            'label' => 'Duplicate',
            'component_type' => 'text_input',
            'sort_order' => 0
        ]);

        $result = $this->validator->validate($page);
        
        $this->assertFalse($result['valid']);
        $this->assertContains("Duplicate field key detected: 'username'. Keys must be unique across the entire page.", $result['errors']);
    }

    public function test_it_detects_missing_bindings_for_input_fields()
    {
        $page = PageDefinition::factory()->create([
            'feature_version_id' => $this->version->id,
        ]);
        $step = $page->steps()->create(['title' => 'Step 1', 'step_key' => 's1', 'sort_order' => 0]);
        
        $step->fields()->create([
            'field_key' => 'amount',
            'label' => 'Amount',
            'component_type' => 'amount_input',
            'sort_order' => 0
        ]);

        $result = $this->validator->validate($page);
        
        $this->assertFalse($result['valid']);
        $this->assertContains("Field 'Amount' (key: amount) is missing a data binding.", $result['errors']);
    }
}
