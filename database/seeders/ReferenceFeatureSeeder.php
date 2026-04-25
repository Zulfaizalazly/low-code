<?php

namespace Database\Seeders;

use App\Models\User;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Registry\FlowDefinition;
use App\Studio\Registry\PageDefinition;
use Illuminate\Database\Seeder;

class ReferenceFeatureSeeder extends Seeder
{
    public function run(): void
    {
        // Use existing users (created by DatabaseSeeder)
        $hq = User::where('role', 'hq_admin')->first();
        $staff = User::where('role', 'branch_staff')->first();
        $manager = User::where('role', 'branch_manager')->first();

        if (!$hq) {
            $this->command->error('HQ Admin user not found. Please ensure DatabaseSeeder creates users first.');
            return;
        }

        // 1. Create "New Pledge" Feature
        $feature = Feature::create([
            'key' => 'new-pledge',
            'name' => 'New Pledge Intake',
            'domain' => 'Facility',
            'status' => 'published',
        ]);

        $version = $feature->versions()->create([
            'version_no' => 1,
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $hq->id,
        ]);

        // 2. Create Flow Definition
        $flow = FlowDefinition::create([
            'feature_version_id' => $version->id,
            'key' => 'intake-flow',
            'name' => 'Intake Processing Flow',
            'trigger_type' => 'manual_entry',
            'entry_mode' => 'user_launch',
            'is_primary' => true,
        ]);

        // Nodes
        $trigger = $flow->nodes()->create([
            'node_key' => 'start',
            'node_type' => 'trigger',
            'label' => 'Form Submitted',
            'position_x' => 100,
            'position_y' => 100,
        ]);

        $regCustomer = $flow->nodes()->create([
            'node_key' => 'reg_customer',
            'node_type' => 'command',
            'label' => 'Register Customer',
            'config' => [
                'command_class' => 'App\Domain\Customer\Commands\RegisterCustomer',
                'payload_mapping' => [
                    'name' => 'form.name',
                    'icNumber' => 'form.ic_number',
                    'email' => 'form.email',
                ]
            ],
            'position_x' => 300,
            'position_y' => 100,
        ]);

        $createFacility = $flow->nodes()->create([
            'node_key' => 'create_facility',
            'node_type' => 'command',
            'label' => 'Create Facility',
            'config' => [
                'command_class' => 'App\Domain\Facility\Commands\CreateFacility',
                'payload_mapping' => [
                    'customerId' => 'nodes.reg_customer.output.id',
                    'productCode' => 'GOLD_STANDARD',
                    'branchId' => 'auth.branch_id',
                    'entityId' => 'auth.entity_id',
                    'principalAmount' => 'form.amount',
                    'items' => 'form.items',
                ]
            ],
            'position_x' => 500,
            'position_y' => 100,
        ]);

        $end = $flow->nodes()->create([
            'node_key' => 'end',
            'node_type' => 'end',
            'label' => 'Finish',
            'position_x' => 700,
            'position_y' => 100,
        ]);

        // Edges
        $flow->edges()->create(['source_node_id' => $trigger->id, 'target_node_id' => $regCustomer->id]);
        $flow->edges()->create(['source_node_id' => $regCustomer->id, 'target_node_id' => $createFacility->id]);
        $flow->edges()->create(['source_node_id' => $createFacility->id, 'target_node_id' => $end->id]);

        // 3. Create Page Definition
        $page = PageDefinition::create([
            'feature_version_id' => $version->id,
            'key' => 'intake-form',
            'name' => 'Intake Details',
            'page_type' => 'workflow_form',
            'is_entry_page' => true,
        ]);

        $step1 = $page->steps()->create([
            'step_key' => 'customer_step',
            'title' => 'Customer Info',
            'sort_order' => 1,
        ]);
        $step1->fields()->create([
            'field_key' => 'name',
            'label' => 'Full Name',
            'component_type' => 'input_text',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        $step1->fields()->create([
            'field_key' => 'ic_number',
            'label' => 'IC Number',
            'component_type' => 'input_text',
            'is_required' => true,
            'sort_order' => 2,
        ]);
        $step1->fields()->create([
            'field_key' => 'email',
            'label' => 'Email Address',
            'component_type' => 'input_email',
            'sort_order' => 3,
        ]);

        $step2 = $page->steps()->create([
            'step_key' => 'facility_step',
            'title' => 'Facility Details',
            'sort_order' => 2,
        ]);
        $step2->fields()->create([
            'field_key' => 'amount',
            'label' => 'Requested Amount',
            'component_type' => 'input_number',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        $step2->fields()->create([
            'field_key' => 'items',
            'label' => 'Item Details',
            'component_type' => 'input_hidden',
            'default_value' => [['item_type' => 'Jewelry', 'weight_grams' => 10, 'purity' => 916]],
            'sort_order' => 2,
        ]);

        // 4. Create Menu Item
        $version->menuItems()->create([
            'menu_key' => 'pledge_intake',
            'label' => 'New Pledge',
            'icon' => 'plus-circle',
            'route_key' => 'f/new-pledge',
            'sort_order' => 1,
        ]);

        $this->command->info('Reference feature (new-pledge) seeded successfully!');
    }
}
