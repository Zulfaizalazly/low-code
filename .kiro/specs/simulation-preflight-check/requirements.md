# Requirements Document

## Introduction

Ciri Simulation Preflight Check memperkenalkan fasa semakan kesediaan (readiness check) sebelum simulasi dry-run dijalankan dalam Flow Builder. Apabila pengguna (Ketua Pegawai Ar-Rahnu) menekan butang "Simulate", modal akan memaparkan imbasan nod-demi-nod secara animasi untuk mengesan konfigurasi yang tidak lengkap. Nod yang gagal semakan akan dikumpulkan dalam satu borang inline supaya pengguna boleh melengkapkan semua medan yang hilang tanpa meninggalkan modal. Simulasi dry-run hanya boleh dilancarkan setelah semua nod lulus semakan preflight.

## Glossary

- **Flow_Canvas**: Komponen Vue utama (`FlowCanvas.vue`) yang memaparkan kanvas visual Vue Flow beserta nod, edge, toolbar, dan panel inspector.
- **Preflight_Checker**: Modul frontend baharu yang mengimbas setiap nod dalam flow dan menentukan sama ada konfigurasi nod lengkap berdasarkan peraturan medan yang ditetapkan oleh `configFields`.
- **Simulation_Modal**: Komponen modal (`FlowSimulationModal.vue`) yang kini akan mengandungi dua fasa — preflight check dan simulasi dry-run.
- **Node_Inspector**: Panel sisi kanan (`NodeInspector.vue`) yang memaparkan medan konfigurasi dinamik berdasarkan jenis nod; menjadi sumber kebenaran (source of truth) untuk medan yang diperlukan.
- **Config_Fields**: Senarai medan konfigurasi yang dikira secara computed dalam Node_Inspector, menentukan medan wajib bagi setiap jenis nod (trigger, command, decision, approval, notification, document, gl_action, formula, payment_gateway, vault_action, api_request, tawarruq_calc, generate_pdf).
- **Flow_Validator**: Kelas backend PHP (`FlowValidator.php`) yang mengesahkan struktur flow (nod trigger/end, orphan, kitaran, dan semakan config asas).
- **Preflight_Result**: Objek keputusan imbasan yang mengandungi status lulus/gagal bagi setiap nod beserta senarai medan yang hilang.
- **Inline_Fix_Form**: Borang pembetulan dalam modal yang dikumpulkan mengikut nod, membolehkan pengguna mengisi medan yang hilang dan menyimpan semula ke konfigurasi nod.
- **Flow_Builder_Controller**: Pengawal API Laravel (`FlowBuilderController.php`) yang mengendalikan endpoint save, validate, dan simulate.
- **Dry_Run_Simulation**: Proses simulasi sedia ada yang menjalankan flow dalam mod kering (dry run) melalui `FlowOrchestrator` dan mengembalikan laluan pelaksanaan serta output nod.

## Requirements

### Requirement 1: Preflight Check Initiation

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu modal simulasi membuka fasa preflight check terlebih dahulu apabila saya menekan "Simulate", supaya saya dapat melihat status kesediaan setiap nod sebelum menjalankan simulasi.

#### Acceptance Criteria

1. WHEN the user clicks the "Simulate" button on the Flow_Canvas toolbar, THE Simulation_Modal SHALL open and display the preflight check phase as the initial view.
2. THE Simulation_Modal SHALL NOT display the dry-run simulation interface until the preflight check phase is completed with all nodes passing.
3. WHEN the Simulation_Modal opens in preflight phase, THE Preflight_Checker SHALL begin scanning nodes automatically without requiring additional user action.

### Requirement 2: Animated Node Scanning Progress

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu melihat animasi imbasan nod-demi-nod dengan bar kemajuan, supaya saya tahu proses semakan sedang berjalan dan nod mana yang sedang disemak.

#### Acceptance Criteria

1. WHEN the preflight scan begins, THE Simulation_Modal SHALL display an animated progress bar that advances as each node is scanned.
2. WHILE the preflight scan is in progress, THE Simulation_Modal SHALL display the label and type of the node currently being scanned.
3. WHEN a node scan completes, THE Simulation_Modal SHALL display a pass (✅) or fail (❌) indicator next to the scanned node entry before proceeding to the next node.
4. THE Preflight_Checker SHALL scan nodes sequentially with a visible per-node delay to create an agent-like scanning animation.

### Requirement 3: Config Completeness Validation Rules

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu semakan preflight mengesan semua medan konfigurasi yang hilang atau tidak lengkap bagi setiap jenis nod, supaya simulasi tidak gagal kerana konfigurasi yang tidak lengkap.

#### Acceptance Criteria

1. THE Preflight_Checker SHALL use the same field definitions as Config_Fields in Node_Inspector to determine required fields for each node type.
2. WHEN scanning a command node, THE Preflight_Checker SHALL verify that the `command_class` field is populated.
3. WHEN scanning a decision node, THE Preflight_Checker SHALL verify that at least one of `expression` or `condition_type` is populated.
4. WHEN scanning a notification node, THE Preflight_Checker SHALL verify that `channel`, `recipient`, and `template_key` fields are populated.
5. WHEN scanning a formula node, THE Preflight_Checker SHALL verify that `formula_key` and `result_path` fields are populated.
6. WHEN scanning a document node, THE Preflight_Checker SHALL verify that `template_key` and `output_format` fields are populated.
7. WHEN scanning a gl_action node, THE Preflight_Checker SHALL verify that `transaction_code` and `amount_path` fields are populated.
8. WHEN scanning an approval node, THE Preflight_Checker SHALL verify that `assigned_role` field is populated.
9. WHEN scanning a payment_gateway node, THE Preflight_Checker SHALL verify that `provider`, `amount`, and `type` fields are populated.
10. WHEN scanning a payment_gateway node WHILE a provider is selected, THE Preflight_Checker SHALL verify that the provider-specific credential fields are populated (e.g., `credentials.collection_id` and `credentials.api_key` for billplz).
11. WHEN scanning a vault_action node, THE Preflight_Checker SHALL verify that `action` and `marhun_id` fields are populated.
12. WHEN scanning an api_request node, THE Preflight_Checker SHALL verify that `method`, `url`, and `output_key` fields are populated.
13. WHEN scanning an api_request node WHILE `auth_type` is set to `bearer`, THE Preflight_Checker SHALL verify that `auth_token` is populated.
14. WHEN scanning an api_request node WHILE `auth_type` is set to `basic`, THE Preflight_Checker SHALL verify that `auth_username` and `auth_password` are populated.
15. WHEN scanning a trigger node, THE Preflight_Checker SHALL verify that `trigger_type` field is populated.
16. WHEN scanning a tawarruq_calc node, THE Preflight_Checker SHALL verify that `marhun_value`, `margin_rate`, `ltv_ratio`, `ujrah_rate`, `tenure_months`, and `output_key` fields are populated.
17. WHEN scanning a generate_pdf node, THE Preflight_Checker SHALL verify that `template_id` field is populated.

### Requirement 4: Preflight Results Display

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu melihat ringkasan keputusan preflight yang jelas selepas imbasan selesai, supaya saya tahu nod mana yang lulus dan mana yang gagal.

#### Acceptance Criteria

1. WHEN the preflight scan completes, THE Simulation_Modal SHALL display a summary showing the total number of nodes scanned, nodes passed, and nodes failed.
2. WHEN all nodes pass the preflight check, THE Simulation_Modal SHALL display a success state and enable the "Proceed to Simulation" action.
3. WHEN one or more nodes fail the preflight check, THE Simulation_Modal SHALL display the failed nodes grouped by node label and type, listing each missing field.
4. THE Simulation_Modal SHALL display a pass indicator (✅) for each node that has complete configuration and a fail indicator (❌) for each node with missing fields.

### Requirement 5: Inline Fix Form for Missing Fields

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu mengisi medan yang hilang terus dalam modal tanpa perlu menutup modal dan mencari setiap nod satu persatu, supaya saya dapat membetulkan semua isu dengan cepat.

#### Acceptance Criteria

1. WHEN one or more nodes fail the preflight check, THE Simulation_Modal SHALL compile all missing fields into a single inline form within the modal.
2. THE Inline_Fix_Form SHALL group missing fields by their parent node, displaying the node label and node type as section headers.
3. THE Inline_Fix_Form SHALL render each missing field using the same field type as defined in Config_Fields (select, text, textarea, json, number).
4. WHEN a field type is `select`, THE Inline_Fix_Form SHALL display the same options as defined in Config_Fields for that field.
5. WHEN a field type is `select` with `command_class` key, THE Inline_Fix_Form SHALL display the available commands list passed from Flow_Canvas.
6. WHEN the user fills in a field in the Inline_Fix_Form and clicks "Save & Re-check", THE Simulation_Modal SHALL write the values back to the correct node configs in the Flow_Canvas nodes array using the `updateNodeConfig` pattern.
7. WHEN the user saves the inline fixes, THE Preflight_Checker SHALL re-run the preflight scan to verify all fields are now complete.

### Requirement 6: Transition to Dry-Run Simulation Phase

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu beralih ke fasa simulasi dry-run setelah semua nod lulus semakan, supaya saya dapat menguji aliran proses dengan yakin bahawa semua konfigurasi lengkap.

#### Acceptance Criteria

1. WHEN all nodes pass the preflight check, THE Simulation_Modal SHALL display a "Proceed to Simulation" button.
2. WHEN the user clicks "Proceed to Simulation", THE Simulation_Modal SHALL transition to the dry-run simulation phase displaying the existing input payload editor and execution path timeline.
3. THE dry-run simulation phase SHALL retain the same functionality as the current Simulation_Modal (JSON payload editor, launch simulation button, execution path timeline with node outputs).
4. WHEN the user is in the dry-run simulation phase, THE Simulation_Modal SHALL provide a "Back to Preflight" action to return to the preflight results view.

### Requirement 7: Config Field Definition Extraction

**User Story:** Sebagai pembangun, saya mahu logik penentuan medan wajib diekstrak ke modul yang boleh dikongsi, supaya Preflight_Checker dan Node_Inspector menggunakan sumber kebenaran yang sama tanpa duplikasi kod.

#### Acceptance Criteria

1. THE system SHALL extract the Config_Fields logic into a shared utility module that both Node_Inspector and Preflight_Checker can import.
2. THE shared utility module SHALL accept a node type and current config as parameters and return the list of config field definitions.
3. WHEN the shared utility module is used by Node_Inspector, THE Node_Inspector SHALL produce identical field definitions as the current implementation.
4. WHEN a new node type is added to the shared utility module, THE Preflight_Checker SHALL automatically validate the new node type fields without additional code changes.

### Requirement 8: Node Config Writeback

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu nilai yang saya isi dalam borang pembetulan inline disimpan semula ke nod yang betul dalam kanvas, supaya perubahan saya tidak hilang apabila saya menutup modal.

#### Acceptance Criteria

1. WHEN the user saves fixes from the Inline_Fix_Form, THE Simulation_Modal SHALL emit update events that write each field value back to the correct node in the Flow_Canvas nodes array.
2. THE writeback mechanism SHALL support nested config keys (e.g., `mapping.field_name`, `credentials.api_key`) using the same dot-notation logic as Node_Inspector.
3. WHEN the writeback completes, THE Flow_Canvas SHALL mark the flow as dirty (`isDirty = true`) to trigger auto-save behavior.
4. IF the writeback fails for any node, THEN THE Simulation_Modal SHALL display an error message identifying the affected node and field.

### Requirement 9: Preflight Check Re-run

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu dapat menjalankan semula semakan preflight selepas membetulkan medan, supaya saya dapat mengesahkan semua isu telah diselesaikan.

#### Acceptance Criteria

1. WHEN the user is viewing preflight results with failures, THE Simulation_Modal SHALL display a "Re-check" button.
2. WHEN the user clicks "Re-check", THE Preflight_Checker SHALL re-scan all nodes and update the results display.
3. WHEN the re-check scan completes with all nodes passing, THE Simulation_Modal SHALL transition to the success state and enable the "Proceed to Simulation" action.
