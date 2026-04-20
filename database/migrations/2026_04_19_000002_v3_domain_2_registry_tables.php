<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. features
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('domain'); // facility, customer, payment, reporting
            $table->string('icon')->nullable();
            $table->string('status')->default('draft'); // draft, published, archived
            $table->string('default_route_key')->nullable();
            $table->string('scope_level')->default('platform'); // platform, entity, branch
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->index(['domain', 'status']);
            $table->index(['key']);
        });

        // 2. feature_versions
        Schema::create('feature_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->integer('version_no');
            $table->string('status')->default('draft');
            // draft, in_review, approved, published, archived, rolled_back
            $table->string('checksum')->nullable();
            $table->text('change_summary')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('rollback_from_version_id')->nullable();
            $table->timestamps();
            $table->unique(['feature_id', 'version_no']);
            $table->index(['feature_id', 'status']);
        });

        // 3. flow_definitions
        Schema::create('flow_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->string('trigger_type'); // manual_entry, domain_event, schedule, api_trigger
            $table->json('trigger_config')->nullable();
            $table->string('entry_mode')->default('user_launch'); // user_launch, auto, event_driven
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->index(['feature_version_id']);
            $table->unique(['feature_version_id', 'key']);
        });

        // 4. flow_nodes
        Schema::create('flow_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_definition_id')->constrained()->cascadeOnDelete();
            $table->string('node_key');
            $table->string('node_type');
            $table->string('label');
            $table->json('config')->nullable();
            $table->integer('position_x')->default(0);
            $table->integer('position_y')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['flow_definition_id']);
            $table->unique(['flow_definition_id', 'node_key']);
        });

        // 5. flow_edges
        Schema::create('flow_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_definition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_node_id')->constrained('flow_nodes')->cascadeOnDelete();
            $table->foreignId('target_node_id')->constrained('flow_nodes')->cascadeOnDelete();
            $table->string('condition_type')->default('always'); // always, expression, outcome
            $table->json('condition_config')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
            $table->index(['flow_definition_id']);
            $table->index(['source_node_id']);
            $table->index(['target_node_id']);
        });

        // 6. page_definitions
        Schema::create('page_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->string('page_type'); // workflow_form, dashboard, detail_view, listing, approval_view
            $table->string('layout_type')->default('single_column');
            $table->string('route_key')->nullable();
            $table->boolean('is_entry_page')->default(false);
            $table->json('config')->nullable();
            $table->timestamps();
            $table->index(['feature_version_id']);
            $table->unique(['feature_version_id', 'key']);
        });

        // 7. page_sections
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_definition_id')->constrained()->cascadeOnDelete();
            $table->string('section_key');
            $table->string('title')->nullable();
            $table->string('component_type');
            $table->string('layout_span')->default('full'); // full, half, third
            $table->integer('sort_order')->default(0);
            $table->json('config')->nullable();
            $table->timestamps();
            $table->index(['page_definition_id']);
        });

        // 8. form_steps
        Schema::create('form_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_definition_id')->constrained()->cascadeOnDelete();
            $table->string('step_key');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('entity_binding')->nullable(); // e.g., 'facility_nominees'
            $table->boolean('is_required')->default(true);
            $table->unsignedBigInteger('visibility_rule_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('config')->nullable();
            $table->timestamps();
            $table->index(['page_definition_id']);
            $table->unique(['page_definition_id', 'step_key']);
        });

        // 9. form_fields
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_step_id')->constrained()->cascadeOnDelete();
            $table->string('field_key');
            $table->string('label');
            $table->string('component_type');
            $table->string('data_type')->default('string');
            $table->boolean('is_required')->default(false);
            $table->json('default_value')->nullable();
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('config')->nullable();
            $table->timestamps();
            $table->index(['form_step_id']);
            $table->unique(['form_step_id', 'field_key']);
        });

        // 10. field_bindings
        Schema::create('field_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_field_id')->constrained()->cascadeOnDelete();
            $table->string('binding_type');
            $table->string('target_entity')->nullable(); // e.g., 'facility_nominees'
            $table->string('target_path')->nullable();   // e.g., 'ic_number'
            $table->string('write_mode')->default('create'); // create, update, upsert
            $table->string('read_mode')->default('direct');  // direct, computed, lookup
            $table->string('transformer_key')->nullable();   // e.g., 'uppercase_ic'
            $table->timestamps();
            $table->index(['form_field_id']);
        });

        // 11. rule_sets
        Schema::create('rule_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->string('rule_type');
            $table->string('scope_type')->default('feature');
            $table->string('target_type')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
            $table->index(['feature_version_id']);
            $table->unique(['feature_version_id', 'key']);
        });

        // 12. rule_rows
        Schema::create('rule_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_set_id')->constrained()->cascadeOnDelete();
            $table->integer('priority')->default(0);
            $table->json('conditions'); // e.g., {"product_code": "GOLD_PLUS"}
            $table->json('result');     // e.g., {"require_nominee": true}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['rule_set_id', 'is_active']);
        });

        // 13. formula_definitions
        Schema::create('formula_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->text('expression');
            $table->json('input_schema')->nullable();
            $table->json('output_schema')->nullable();
            $table->string('rounding_policy')->default('round_half_up');
            $table->json('config')->nullable();
            $table->timestamps();
            $table->index(['feature_version_id']);
            $table->unique(['feature_version_id', 'key']);
        });

        // 14. document_templates
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->string('template_type');
            $table->json('content_schema')->nullable();
            $table->string('render_engine')->default('blade');
            $table->json('bindings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['feature_version_id']);
            $table->unique(['feature_version_id', 'key']);
        });

        // 15. feature_permissions
        Schema::create('feature_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->string('role_key');
            $table->string('permission_key');
            $table->string('access_mode')->default('full');
            $table->json('config')->nullable();
            $table->timestamps();
            $table->unique(['feature_version_id', 'role_key', 'permission_key']);
        });

        // 16. feature_menu_items
        Schema::create('feature_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->string('menu_key');
            $table->string('label');
            $table->string('icon')->nullable();
            $table->string('parent_menu_key')->nullable();
            $table->string('route_key');
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('visibility_rule_id')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->index(['feature_version_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_menu_items');
        Schema::dropIfExists('feature_permissions');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('formula_definitions');
        Schema::dropIfExists('rule_rows');
        Schema::dropIfExists('rule_sets');
        Schema::dropIfExists('field_bindings');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('form_steps');
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('page_definitions');
        Schema::dropIfExists('flow_edges');
        Schema::dropIfExists('flow_nodes');
        Schema::dropIfExists('flow_definitions');
        Schema::dropIfExists('feature_versions');
        Schema::dropIfExists('features');
    }
};
