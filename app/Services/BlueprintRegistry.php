<?php

namespace App\Services;

class BlueprintRegistry
{
    public static function getBlueprint(string $key): ?array
    {
        $blueprints = [
            'pledge_intake' => self::getPledgeIntakeBlueprint(),
        ];

        return $blueprints[$key] ?? null;
    }

    private static function getPledgeIntakeBlueprint(): array
    {
        return [
            'name' => 'Standard Pledge Intake (2026)',
            'domain' => 'Facility',
            // Flow Builder JSON structure
            'flow_definition' => [
                'nodes' => [
                    [
                        'node_key' => 'trigger_start',
                        'node_type' => 'trigger',
                        'label' => 'New Customer Request',
                        'position_x' => 400,
                        'position_y' => 50,
                        'config' => []
                    ],
                    [
                        'node_key' => 'amla_check',
                        'node_type' => 'command',
                        'label' => 'AMLA Validation',
                        'position_x' => 400,
                        'position_y' => 200,
                        'config' => ['command_class' => 'App\Commands\AmlaCheckCommand']
                    ],
                    [
                        'node_key' => 'calc_margin',
                        'node_type' => 'formula',
                        'label' => 'Calculate Margin & Ujrah',
                        'position_x' => 400,
                        'position_y' => 350,
                        'config' => ['formula_key' => 'standard_margin_2026']
                    ],
                    [
                        'node_key' => 'high_value_check',
                        'node_type' => 'decision',
                        'label' => 'High Value?',
                        'position_x' => 400,
                        'position_y' => 500,
                        'config' => ['condition_field' => 'margin_amount', 'expression' => '> 50000']
                    ],
                    [
                        'node_key' => 'supervisor_approval',
                        'node_type' => 'approval',
                        'label' => 'Supervisor Approval',
                        'position_x' => 600,
                        'position_y' => 650,
                        'config' => ['role' => 'supervisor']
                    ],
                    [
                        'node_key' => 'gen_surat_pajak',
                        'node_type' => 'document',
                        'label' => 'Generate Surat Pajak',
                        'position_x' => 400,
                        'position_y' => 800,
                        'config' => ['template_id' => 'surat_pajak_v1']
                    ],
                    [
                        'node_key' => 'end_flow',
                        'node_type' => 'end',
                        'label' => 'End',
                        'position_x' => 400,
                        'position_y' => 950,
                        'config' => []
                    ],
                ],
                'edges' => [
                    [
                        'source_node_key' => 'trigger_start',
                        'target_node_key' => 'amla_check',
                        'condition_type' => 'always',
                    ],
                    [
                        'source_node_key' => 'amla_check',
                        'target_node_key' => 'calc_margin',
                        'condition_type' => 'always',
                    ],
                    [
                        'source_node_key' => 'calc_margin',
                        'target_node_key' => 'high_value_check',
                        'condition_type' => 'always',
                    ],
                    [
                        'source_node_key' => 'high_value_check',
                        'target_node_key' => 'supervisor_approval',
                        'condition_type' => 'is_true',
                    ],
                    [
                        'source_node_key' => 'high_value_check',
                        'target_node_key' => 'gen_surat_pajak',
                        'condition_type' => 'is_false',
                    ],
                    [
                        'source_node_key' => 'supervisor_approval',
                        'target_node_key' => 'gen_surat_pajak',
                        'condition_type' => 'always',
                    ],
                    [
                        'source_node_key' => 'gen_surat_pajak',
                        'target_node_key' => 'end_flow',
                        'condition_type' => 'always',
                    ],
                ]
            ],
            // Page Builder JSON structure
            'page_definition' => [
                'steps' => [
                    [
                        'step_key' => 'step_customer',
                        'title' => 'Customer KYC',
                        'description' => 'Mandatory identity verification',
                        'entity_binding' => 'customer',
                        'sort_order' => 0,
                        'fields' => [
                            [
                                'field_key' => 'ic_no',
                                'label' => 'IC Number',
                                'component_type' => 'ic_input',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => ['pattern' => '^\d{6}-\d{2}-\d{4}$'],
                                'binding' => ['target_entity' => 'customer', 'target_path' => 'ic_number']
                            ],
                            [
                                'field_key' => 'full_name',
                                'label' => 'Full Name (as per IC)',
                                'component_type' => 'text_input',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'customer', 'target_path' => 'name']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_gold',
                        'title' => 'Gold Valuation',
                        'description' => 'Capture pledged items',
                        'entity_binding' => 'pledge_items',
                        'sort_order' => 1,
                        'fields' => [
                            [
                                'field_key' => 'gold_items',
                                'label' => 'Pledged Items Details',
                                'component_type' => 'gold_repeater',
                                'data_type' => 'collection',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'pledge_items', 'target_path' => 'items']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_summary',
                        'title' => 'Summary & Documentation',
                        'description' => 'Review and upload supporting docs',
                        'entity_binding' => 'pledge_summary',
                        'sort_order' => 2,
                        'fields' => [
                            [
                                'field_key' => 'doc_upload',
                                'label' => 'Upload Supporting Documents (Optional)',
                                'component_type' => 'file_upload',
                                'data_type' => 'file',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => '', 'target_path' => '']
                            ],
                            [
                                'field_key' => 'terms_agree',
                                'label' => 'I confirm the valuation is accurate',
                                'component_type' => 'checkbox',
                                'data_type' => 'boolean',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => '', 'target_path' => '']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
