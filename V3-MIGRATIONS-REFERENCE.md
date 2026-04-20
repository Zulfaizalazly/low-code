# V3 Database Migrations Reference

> Ready-to-use Laravel migration schemas for Arrahnumation V3.
> All tables are organized by data domain.

---

## Domain 1: Operational Data

### customers
```php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('ic_number')->unique();
    $table->string('phone')->nullable();
    $table->string('email')->nullable();
    $table->text('address')->nullable();
    $table->date('date_of_birth')->nullable();
    $table->string('customer_type')->default('individual');
    $table->string('status')->default('active');
    $table->timestamps();
    $table->index(['ic_number', 'status']);
});
```

### customer_contacts
```php
Schema::create('customer_contacts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->string('contact_type'); // phone, email, whatsapp
    $table->string('value');
    $table->boolean('is_primary')->default(false);
    $table->timestamps();
    $table->index(['customer_id', 'contact_type']);
});
```

### facilities
```php
Schema::create('facilities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained();
    $table->string('product_code');
    $table->unsignedBigInteger('branch_id');
    $table->unsignedBigInteger('entity_id');
    $table->string('facility_number')->unique();
    $table->decimal('principal_amount', 14, 2)->default(0);
    $table->integer('tenure_months')->default(6);
    $table->decimal('profit_rate', 8, 4)->default(0);
    $table->string('status')->default('draft');
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('disbursed_at')->nullable();
    $table->timestamp('matured_at')->nullable();
    $table->timestamps();
    $table->index(['customer_id', 'status']);
    $table->index(['branch_id', 'status']);
    $table->index(['facility_number']);
});
```

### facility_items
```php
Schema::create('facility_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
    $table->string('item_type'); // gold_jewellery, gold_bar, gold_coin
    $table->string('description')->nullable();
    $table->decimal('weight_grams', 10, 4);
    $table->decimal('purity', 5, 2); // e.g., 91.60 for 916
    $table->decimal('valuation_amount', 14, 2)->default(0);
    $table->string('status')->default('active');
    $table->string('vault_location')->nullable();
    $table->timestamps();
    $table->index(['facility_id', 'status']);
});
```

### facility_nominees
```php
Schema::create('facility_nominees', function (Blueprint $table) {
    $table->id();
    $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('ic_number');
    $table->string('relationship'); // spouse, child, parent, sibling, other
    $table->string('phone')->nullable();
    $table->text('address')->nullable();
    $table->boolean('is_primary')->default(false);
    $table->timestamps();
    $table->index(['facility_id']);
});
```

### valuations
```php
Schema::create('valuations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('facility_id')->constrained();
    $table->unsignedBigInteger('facility_item_id')->nullable();
    $table->decimal('gold_price_per_gram', 14, 2);
    $table->decimal('weight_grams', 10, 4);
    $table->decimal('purity_percentage', 5, 2);
    $table->decimal('gross_value', 14, 2);
    $table->decimal('ltv_percentage', 5, 2);
    $table->decimal('valuation_amount', 14, 2);
    $table->unsignedBigInteger('valued_by')->nullable();
    $table->timestamp('valued_at')->nullable();
    $table->timestamps();
    $table->index(['facility_id']);
});
```

### approval_tasks
```php
Schema::create('approval_tasks', function (Blueprint $table) {
    $table->id();
    $table->string('approvable_type');
    $table->unsignedBigInteger('approvable_id');
    $table->string('approval_tier'); // tier_1, tier_2, tier_3
    $table->unsignedBigInteger('assigned_to')->nullable();
    $table->string('assigned_role')->nullable();
    $table->string('status')->default('pending'); // pending, approved, rejected, escalated
    $table->string('decision')->nullable();
    $table->text('remarks')->nullable();
    $table->timestamp('decided_at')->nullable();
    $table->timestamps();
    $table->index(['approvable_type', 'approvable_id']);
    $table->index(['assigned_to', 'status']);
    $table->index(['status']);
});
```

### payment_transactions
```php
Schema::create('payment_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('facility_id')->constrained();
    $table->string('payment_type'); // disbursement, repayment, profit, penalty, refund
    $table->decimal('amount', 14, 2);
    $table->string('payment_method')->nullable(); // cash, bank_transfer, cheque
    $table->string('reference_number')->nullable();
    $table->unsignedBigInteger('received_by')->nullable();
    $table->unsignedBigInteger('branch_id');
    $table->string('status')->default('pending'); // pending, completed, failed, reversed
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
    $table->index(['facility_id', 'payment_type']);
    $table->index(['branch_id', 'status']);
});
```

### journal_entries
```php
Schema::create('journal_entries', function (Blueprint $table) {
    $table->id();
    $table->string('entry_number')->unique();
    $table->string('reference_type')->nullable();
    $table->unsignedBigInteger('reference_id')->nullable();
    $table->text('description')->nullable();
    $table->unsignedBigInteger('posted_by')->nullable();
    $table->timestamp('posted_at')->nullable();
    $table->boolean('is_balanced')->default(false);
    $table->timestamps();
    $table->index(['reference_type', 'reference_id']);
    $table->index(['entry_number']);
});
```

### journal_entry_lines
```php
Schema::create('journal_entry_lines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
    $table->string('account_code');
    $table->string('account_name');
    $table->decimal('debit_amount', 14, 2)->default(0);
    $table->decimal('credit_amount', 14, 2)->default(0);
    $table->text('description')->nullable();
    $table->timestamps();
    $table->index(['journal_entry_id']);
    $table->index(['account_code']);
});
```

### documents
```php
Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->string('documentable_type');
    $table->unsignedBigInteger('documentable_id');
    $table->string('document_type'); // contract, receipt, letter, report
    $table->unsignedBigInteger('template_id')->nullable();
    $table->integer('template_version')->nullable();
    $table->string('file_path');
    $table->string('file_name');
    $table->unsignedBigInteger('generated_by')->nullable();
    $table->timestamp('generated_at')->nullable();
    $table->timestamps();
    $table->index(['documentable_type', 'documentable_id']);
});
```

### notification_logs
```php
Schema::create('notification_logs', function (Blueprint $table) {
    $table->id();
    $table->string('notifiable_type');
    $table->unsignedBigInteger('notifiable_id');
    $table->string('channel'); // sms, email, push, whatsapp
    $table->string('recipient');
    $table->string('subject')->nullable();
    $table->text('body')->nullable();
    $table->string('status')->default('pending'); // pending, sent, delivered, failed
    $table->timestamp('sent_at')->nullable();
    $table->text('failed_reason')->nullable();
    $table->timestamps();
    $table->index(['notifiable_type', 'notifiable_id']);
    $table->index(['channel', 'status']);
});
```

---

## Domain 2: Registry Data (Feature Configuration)

### features
```php
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
```

### feature_versions
```php
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
```

### flow_definitions
```php
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
```

### flow_nodes
```php
Schema::create('flow_nodes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('flow_definition_id')->constrained()->cascadeOnDelete();
    $table->string('node_key');
    $table->string('node_type');
    // trigger, step, decision, approval, notification, document,
    // formula, gl_action, api_call, delay_timer, exception_handling, end
    $table->string('label');
    $table->json('config')->nullable();
    $table->integer('position_x')->default(0);
    $table->integer('position_y')->default(0);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
    $table->index(['flow_definition_id']);
    $table->unique(['flow_definition_id', 'node_key']);
});
```

### flow_edges
```php
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
```

### page_definitions
```php
Schema::create('page_definitions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
    $table->string('key');
    $table->string('name');
    $table->string('page_type'); // workflow_form, dashboard, detail_view, listing, approval_view
    $table->string('layout_type')->default('single_column');
    // single_column, two_column, stepper, tabbed
    $table->string('route_key')->nullable();
    $table->boolean('is_entry_page')->default(false);
    $table->json('config')->nullable();
    $table->timestamps();
    $table->index(['feature_version_id']);
    $table->unique(['feature_version_id', 'key']);
});
```

### page_sections
```php
Schema::create('page_sections', function (Blueprint $table) {
    $table->id();
    $table->foreignId('page_definition_id')->constrained()->cascadeOnDelete();
    $table->string('section_key');
    $table->string('title')->nullable();
    $table->string('component_type');
    // hero_header, form_stepper, summary_panel, document_panel,
    // approval_sidebar, data_table, timeline, alert_banner
    $table->string('layout_span')->default('full'); // full, half, third
    $table->integer('sort_order')->default(0);
    $table->json('config')->nullable();
    $table->timestamps();
    $table->index(['page_definition_id']);
});
```

### form_steps
```php
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
```

### form_fields
```php
Schema::create('form_fields', function (Blueprint $table) {
    $table->id();
    $table->foreignId('form_step_id')->constrained()->cascadeOnDelete();
    $table->string('field_key');
    $table->string('label');
    $table->string('component_type');
    // text_input, ic_input, phone_input, textarea, select,
    // date_picker, amount_input, repeater, checkbox, radio, file_upload
    $table->string('data_type')->default('string');
    // string, integer, decimal, date, boolean, json
    $table->boolean('is_required')->default(false);
    $table->string('default_value')->nullable();
    $table->string('placeholder')->nullable();
    $table->text('help_text')->nullable();
    $table->integer('sort_order')->default(0);
    $table->json('config')->nullable();
    $table->timestamps();
    $table->index(['form_step_id']);
    $table->unique(['form_step_id', 'field_key']);
});
```

### field_bindings
```php
Schema::create('field_bindings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('form_field_id')->constrained()->cascadeOnDelete();
    $table->string('binding_type');
    // model_column, computed_variable, workflow_variable, lookup, constant
    $table->string('target_entity')->nullable(); // e.g., 'facility_nominees'
    $table->string('target_path')->nullable();   // e.g., 'ic_number'
    $table->string('write_mode')->default('create'); // create, update, upsert
    $table->string('read_mode')->default('direct');  // direct, computed, lookup
    $table->string('transformer_key')->nullable();   // e.g., 'uppercase_ic'
    $table->timestamps();
    $table->index(['form_field_id']);
});
```

### rule_sets
```php
Schema::create('rule_sets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
    $table->string('key');
    $table->string('name');
    $table->string('rule_type');
    // validation, visibility, workflow_guard, approval_routing, document_requirement
    $table->string('scope_type')->default('feature');
    // feature, step, field, entity
    $table->string('target_type')->nullable();
    $table->json('config')->nullable();
    $table->timestamps();
    $table->index(['feature_version_id']);
    $table->unique(['feature_version_id', 'key']);
});
```

### rule_rows
```php
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
```

### formula_definitions
```php
Schema::create('formula_definitions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
    $table->string('key');
    $table->string('name');
    $table->text('expression');
    $table->json('input_schema')->nullable();
    $table->json('output_schema')->nullable();
    $table->string('rounding_policy')->default('round_half_up');
    // none, round_half_up, round_down, ceil
    $table->json('config')->nullable();
    $table->timestamps();
    $table->index(['feature_version_id']);
    $table->unique(['feature_version_id', 'key']);
});
```

### document_templates
```php
Schema::create('document_templates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
    $table->string('key');
    $table->string('name');
    $table->string('template_type');
    // contract, receipt, letter, report, notification_template
    $table->json('content_schema')->nullable();
    $table->string('render_engine')->default('blade');
    // blade, pdf_generator, docx
    $table->json('bindings')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->index(['feature_version_id']);
    $table->unique(['feature_version_id', 'key']);
});
```

### feature_permissions
```php
Schema::create('feature_permissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
    $table->string('role_key');       // e.g., 'branch_staff'
    $table->string('permission_key'); // e.g., 'create', 'approve', 'view'
    $table->string('access_mode')->default('full');
    // full, read_only, conditional
    $table->json('config')->nullable();
    $table->timestamps();
    $table->unique(['feature_version_id', 'role_key', 'permission_key']);
});
```

### feature_menu_items
```php
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
```

---

## Domain 3: Publish & Versioning Data

### release_batches
```php
Schema::create('release_batches', function (Blueprint $table) {
    $table->id();
    $table->string('batch_number')->unique();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('status')->default('draft');
    // draft, in_review, approved, published, cancelled
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('approved_by')->nullable();
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
    $table->index(['status']);
});
```

### release_items
```php
Schema::create('release_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('release_batch_id')->constrained()->cascadeOnDelete();
    $table->foreignId('feature_version_id')->constrained();
    $table->string('change_type'); // new_feature, update, hotfix, rollback
    $table->text('change_summary')->nullable();
    $table->timestamps();
    $table->index(['release_batch_id']);
});
```

### publish_validations
```php
Schema::create('publish_validations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
    $table->string('check_type');
    // trigger, page, fields, bindings, rules, permissions,
    // menu, success_path, failure_path, audit, dependencies,
    // simulation, impact_analysis, version_snapshot
    $table->string('check_key');
    $table->string('status'); // passed, failed, warning, skipped
    $table->text('message')->nullable();
    $table->timestamp('validated_at');
    $table->timestamps();
    $table->index(['feature_version_id', 'status']);
});
```

### impact_analysis_reports
```php
Schema::create('impact_analysis_reports', function (Blueprint $table) {
    $table->id();
    $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
    $table->json('affected_branches')->nullable();
    $table->json('affected_roles')->nullable();
    $table->json('affected_documents')->nullable();
    $table->json('affected_reports')->nullable();
    $table->string('risk_level')->default('low'); // low, medium, high, critical
    $table->text('summary')->nullable();
    $table->timestamp('generated_at');
    $table->timestamps();
    $table->index(['feature_version_id']);
});
```

### rollback_logs
```php
Schema::create('rollback_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('feature_version_id')->constrained();
    $table->unsignedBigInteger('rolled_back_from_version');
    $table->text('reason');
    $table->unsignedBigInteger('rolled_back_by');
    $table->timestamp('rolled_back_at');
    $table->timestamps();
    $table->index(['feature_version_id']);
});
```

---

## Domain 4: Runtime & Audit Data

### event_logs
```php
Schema::create('event_logs', function (Blueprint $table) {
    $table->id();
    $table->string('event_type');           // e.g., 'facility.created'
    $table->json('event_payload');
    $table->string('source_type')->nullable();
    $table->unsignedBigInteger('source_id')->nullable();
    $table->timestamp('emitted_at');
    $table->timestamps();
    $table->index(['event_type']);
    $table->index(['source_type', 'source_id']);
    $table->index(['emitted_at']);
});
```

### automation_execution_logs
```php
Schema::create('automation_execution_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('flow_definition_id')->constrained();
    $table->unsignedBigInteger('feature_version_id');
    $table->string('trigger_type');
    $table->string('trigger_source')->nullable();
    $table->string('status')->default('running');
    // running, completed, failed, timed_out, cancelled
    $table->timestamp('started_at');
    $table->timestamp('completed_at')->nullable();
    $table->text('error_message')->nullable();
    $table->timestamps();
    $table->index(['flow_definition_id', 'status']);
    $table->index(['feature_version_id']);
    $table->index(['started_at']);
});
```

### automation_node_logs
```php
Schema::create('automation_node_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('execution_log_id')
          ->constrained('automation_execution_logs')
          ->cascadeOnDelete();
    $table->unsignedBigInteger('flow_node_id');
    $table->string('node_key');
    $table->string('node_type');
    $table->json('input_data')->nullable();
    $table->json('output_data')->nullable();
    $table->string('status'); // pending, running, completed, failed, skipped
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->text('error_message')->nullable();
    $table->timestamps();
    $table->index(['execution_log_id']);
    $table->index(['flow_node_id']);
    $table->index(['status']);
});
```

### command_logs
```php
Schema::create('command_logs', function (Blueprint $table) {
    $table->id();
    $table->string('command_type');         // e.g., 'CreateFacilityApplication'
    $table->json('command_payload');
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('status');               // accepted, processed, failed, rejected
    $table->timestamp('executed_at');
    $table->timestamps();
    $table->index(['command_type', 'status']);
    $table->index(['user_id']);
    $table->index(['executed_at']);
});
```

### ui_submission_logs
```php
Schema::create('ui_submission_logs', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('page_definition_id');
    $table->integer('page_version');
    $table->json('form_data');
    $table->unsignedBigInteger('submitted_by');
    $table->timestamp('submitted_at');
    $table->timestamps();
    $table->index(['page_definition_id']);
    $table->index(['submitted_by']);
    $table->index(['submitted_at']);
});
```

### audit_trails
```php
Schema::create('audit_trails', function (Blueprint $table) {
    $table->id();
    $table->string('auditable_type');
    $table->unsignedBigInteger('auditable_id');
    $table->string('action');               // created, updated, deleted, approved, etc.
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamp('performed_at');
    $table->timestamps();
    $table->index(['auditable_type', 'auditable_id']);
    $table->index(['user_id']);
    $table->index(['action']);
    $table->index(['performed_at']);
});
```

### dead_letter_jobs
```php
Schema::create('dead_letter_jobs', function (Blueprint $table) {
    $table->id();
    $table->string('queue');
    $table->longText('payload');
    $table->longText('exception');
    $table->timestamp('failed_at');
    $table->timestamps();
    $table->index(['queue']);
    $table->index(['failed_at']);
});
```

---

## Domain 5: Scope Overrides

### scoped_overrides
```php
Schema::create('scoped_overrides', function (Blueprint $table) {
    $table->id();
    $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
    $table->string('scope_type');       // branch, product, entity, region
    $table->string('scope_id');         // e.g., branch_id or product_code
    $table->string('target_table');     // which registry table
    $table->string('target_key');       // which record key
    $table->json('override_value');     // the override
    $table->date('effective_from');
    $table->date('effective_to')->nullable();
    $table->timestamps();
    $table->index(['feature_version_id', 'scope_type']);
    $table->index(['scope_type', 'scope_id']);
    $table->index(['effective_from', 'effective_to']);
});
```

---

## Table Count Summary

| Domain | Tables | Count |
|---|---|---|
| Operational Data | customers, customer_contacts, facilities, facility_items, facility_nominees, valuations, approval_tasks, payment_transactions, journal_entries, journal_entry_lines, documents, notification_logs | **12** |
| Registry Data | features, feature_versions, flow_definitions, flow_nodes, flow_edges, page_definitions, page_sections, form_steps, form_fields, field_bindings, rule_sets, rule_rows, formula_definitions, document_templates, feature_permissions, feature_menu_items | **16** |
| Publish & Versioning | release_batches, release_items, publish_validations, impact_analysis_reports, rollback_logs | **5** |
| Runtime & Audit | event_logs, automation_execution_logs, automation_node_logs, command_logs, ui_submission_logs, audit_trails, dead_letter_jobs | **7** |
| Scope Overrides | scoped_overrides | **1** |
| **Total** | | **41** |
