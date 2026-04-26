<?php

namespace App\Services;

class BlueprintRegistry
{
    public static function getBlueprint(string $key): ?array
    {
        $blueprints = [
            'pledge_intake'      => self::getPledgeIntakeBlueprint(),
            'pledge_renewal'     => self::getPledgeRenewalBlueprint(),
            'pledge_redemption'  => self::getPledgeRedemptionBlueprint(),
            'additional_margin'  => self::getAdditionalMarginBlueprint(),
            'margin_call'        => self::getMarginCallBlueprint(),
            'auction_process'    => self::getAuctionProcessBlueprint(),
            'payment_collection' => self::getPaymentCollectionBlueprint(),
            'vault_recon'        => self::getVaultReconBlueprint(),
            'kyc_update'         => self::getKycUpdateBlueprint(),
            'bnm_report'         => self::getBnmReportBlueprint(),
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
                        'config' => [
                            'trigger_type' => 'manual_start',
                            'event_name' => 'START_INTAKE'
                        ]
                    ],
                    [
                        'node_key' => 'fetch_gold_price',
                        'node_type' => 'api_request',
                        'label' => 'Fetch Gold Price (MYR)',
                        'position_x' => 400,
                        'position_y' => 150,
                        'config' => [
                            'url' => 'https://www.goldapi.io/api/XAU/MYR',
                            'method' => 'GET',
                            'headers' => ['x-access-token' => '{{env.GOLDAPI_KEY}}'],
                            'output_key' => 'gold_price_data',
                            'mock_response' => [
                                'status' => 200,
                                'successful' => true,
                                'body' => ['price_gram_24k' => 350.50, 'price_gram_22k' => 322.00]
                            ]
                        ]
                    ],
                    [
                        'node_key' => 'amla_check',
                        'node_type' => 'command',
                        'label' => 'AMLA Validation',
                        'position_x' => 400,
                        'position_y' => 250,
                        'config' => [
                            'command_class' => 'App\Commands\AmlaCheckCommand',
                            'payload_mapping' => [
                                'ic_number' => 'form.ic_no',
                                'name' => 'form.full_name'
                            ]
                        ]
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
                        'position_y' => 450,
                        'config' => ['condition_field' => 'margin_amount', 'expression' => '> 50000']
                    ],
                    [
                        'node_key' => 'supervisor_approval',
                        'node_type' => 'approval',
                        'label' => 'Supervisor Approval',
                        'position_x' => 600,
                        'position_y' => 550,
                        'config' => ['role' => 'supervisor']
                    ],
                    [
                        'node_key' => 'post_gl_entry',
                        'node_type' => 'gl_action',
                        'label' => 'Post GL Entry',
                        'position_x' => 400,
                        'position_y' => 650,
                        'config' => [
                            'command_class' => 'App\Commands\PostGLEntryCommand',
                            'payload_mapping' => [
                                'amount' => 'nodes.calc_margin.output.margin_amount',
                                'reference' => 'form.ic_no'
                            ]
                        ]
                    ],
                    [
                        'node_key' => 'vault_checkin',
                        'node_type' => 'vault_action',
                        'label' => 'Vault Check-In',
                        'position_x' => 400,
                        'position_y' => 750,
                        'config' => [
                            'command_class' => 'App\Commands\VaultCheckInCommand',
                            'payload_mapping' => [
                                'item_id' => 'form.ic_no',
                                'location' => 'Safe Alpha-1'
                            ]
                        ]
                    ],

                    [
                        'node_key' => 'gen_surat_pajak',
                        'node_type' => 'document',
                        'label' => 'Generate Surat Pajak',
                        'position_x' => 400,
                        'position_y' => 850,
                        'config' => ['template_id' => 'surat_pajak_v1']
                    ],
                    [
                        'node_key' => 'send_notification',
                        'node_type' => 'notification',
                        'label' => 'Send Digital Receipt',
                        'position_x' => 400,
                        'position_y' => 950,
                        'config' => ['channel' => 'sms', 'message_template' => 'pledge_success_v1']
                    ],
                    [
                        'node_key' => 'end_flow',
                        'node_type' => 'end',
                        'label' => 'End',
                        'position_x' => 400,
                        'position_y' => 1050,
                        'config' => []
                    ],
                ],
                'edges' => [
                    ['source_node_key' => 'trigger_start', 'target_node_key' => 'fetch_gold_price', 'condition_type' => 'always'],
                    ['source_node_key' => 'fetch_gold_price', 'target_node_key' => 'amla_check', 'condition_type' => 'always'],
                    ['source_node_key' => 'amla_check', 'target_node_key' => 'calc_margin', 'condition_type' => 'always'],
                    ['source_node_key' => 'calc_margin', 'target_node_key' => 'high_value_check', 'condition_type' => 'always'],
                    ['source_node_key' => 'high_value_check', 'target_node_key' => 'supervisor_approval', 'condition_type' => 'is_true'],
                    ['source_node_key' => 'high_value_check', 'target_node_key' => 'post_gl_entry', 'condition_type' => 'is_false'],
                    ['source_node_key' => 'supervisor_approval', 'target_node_key' => 'post_gl_entry', 'condition_type' => 'always'],
                    ['source_node_key' => 'post_gl_entry', 'target_node_key' => 'vault_checkin', 'condition_type' => 'always'],
                    ['source_node_key' => 'vault_checkin', 'target_node_key' => 'gen_surat_pajak', 'condition_type' => 'always'],
                    ['source_node_key' => 'gen_surat_pajak', 'target_node_key' => 'send_notification', 'condition_type' => 'always'],
                    ['source_node_key' => 'send_notification', 'target_node_key' => 'end_flow', 'condition_type' => 'always'],
                ]
            ],
            // Page Builder JSON structure
            'page_definition' => [
                'steps' => [
                    [
                        'step_key' => 'step_customer',
                        'title' => 'Identity & KYC',
                        'description' => 'Automated identity verification via MyKad',
                        'entity_binding' => 'customer',
                        'sort_order' => 0,
                        'fields' => [
                            [
                                'field_key' => 'ic_no',
                                'label' => 'IC Number',
                                'component_type' => 'ic_input',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => ['enable_ocr' => true],
                                'binding' => ['target_entity' => 'customer', 'target_path' => 'ic_number']
                            ],
                            [
                                'field_key' => 'full_name',
                                'label' => 'Full Name',
                                'component_type' => 'text_input',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'customer', 'target_path' => 'name']
                            ],
                            [
                                'field_key' => 'phone',
                                'label' => 'Mobile Number',
                                'component_type' => 'text_input',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => ['placeholder' => '60123456789'],
                                'binding' => ['target_entity' => 'customer', 'target_path' => 'phone_number']
                            ],
                            [
                                'field_key' => 'address',
                                'label' => 'Residential Address',
                                'component_type' => 'textarea',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'customer', 'target_path' => 'address']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_gold',
                        'title' => 'Marhun Valuation',
                        'description' => 'Technical inspection of gold items',
                        'entity_binding' => 'pledge_items',
                        'sort_order' => 1,
                        'fields' => [
                            [
                                'field_key' => 'gold_items',
                                'label' => 'Item Details',
                                'component_type' => 'gold_repeater',
                                'data_type' => 'collection',
                                'is_required' => true,
                                'config' => ['show_density_test' => true, 'show_acid_test' => true],
                                'binding' => ['target_entity' => 'pledge_items', 'target_path' => 'items']
                            ],
                            [
                                'field_key' => 'marhun_photos',
                                'label' => 'Item Photos',
                                'component_type' => 'camera_capture',
                                'data_type' => 'collection',
                                'is_required' => true,
                                'config' => ['min_photos' => 2],
                                'binding' => ['target_entity' => 'pledge_items', 'target_path' => 'photos']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_akad',
                        'title' => 'Offer & Akad',
                        'description' => 'Contractual agreement and acceptance',
                        'entity_binding' => 'akad',
                        'sort_order' => 2,
                        'fields' => [
                            [
                                'field_key' => 'loan_summary',
                                'label' => 'Financing Offer',
                                'component_type' => 'summary_panel',
                                'data_type' => 'object',
                                'is_required' => false,
                                'config' => ['fields' => ['margin_amount', 'ujrah_monthly', 'tenure']],
                                'binding' => ['target_entity' => 'summary', 'target_path' => 'data']
                            ],
                            [
                                'field_key' => 'akad_text',
                                'label' => 'Lafaz Akad',
                                'component_type' => 'html_display',
                                'data_type' => 'string',
                                'is_required' => false,
                                'config' => ['content' => '<p>Saya dengan ini bersetuju untuk menggadaikan barang emas tersebut sebagai jaminan bagi pembiayaan yang diberikan...</p>'],
                                'binding' => ['target_entity' => '', 'target_path' => '']
                            ],
                            [
                                'field_key' => 'confirm_akad',
                                'label' => 'Saya Setuju dengan Akad & Terma di atas',
                                'component_type' => 'checkbox',
                                'data_type' => 'boolean',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'akad', 'target_path' => 'accepted']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_finalize',
                        'title' => 'Completion',
                        'description' => 'Final signatures and document archiving',
                        'entity_binding' => 'pledge_summary',
                        'sort_order' => 3,
                        'fields' => [
                            [
                                'field_key' => 'customer_signature',
                                'label' => 'Customer Signature',
                                'component_type' => 'signature_pad',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'pledge_summary', 'target_path' => 'signature']
                            ],
                            [
                                'field_key' => 'officer_signature',
                                'label' => 'Officer Signature',
                                'component_type' => 'signature_pad',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'pledge_summary', 'target_path' => 'officer_signature']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }


    private static function getPledgeRenewalBlueprint(): array
    {
        return [
            'name' => 'Pledge Renewal (Sambung Pajak)',
            'domain' => 'Facility',
            'flow_definition' => [
                'nodes' => [
                    [
                        'node_key' => 'trigger_start',
                        'node_type' => 'trigger',
                        'label' => 'Renewal Request',
                        'position_x' => 400,
                        'position_y' => 50,
                        'config' => []
                    ],
                    [
                        'node_key' => 'fetch_facility',
                        'node_type' => 'command',
                        'label' => 'Fetch Facility',
                        'position_x' => 400,
                        'position_y' => 150,
                        'config' => ['command_class' => 'App\Commands\FetchFacilityCommand']
                    ],
                    [
                        'node_key' => 'check_overdue',
                        'node_type' => 'decision',
                        'label' => 'Check Overdue',
                        'position_x' => 400,
                        'position_y' => 250,
                        'config' => ['condition_field' => 'is_overdue', 'expression' => '== true']
                    ],
                    [
                        'node_key' => 'tawarruq_calc',
                        'node_type' => 'formula',
                        'label' => 'Tawarruq Calculation',
                        'position_x' => 400,
                        'position_y' => 350,
                        'config' => ['formula_key' => 'renewal_tawarruq_2026']
                    ],
                    [
                        'node_key' => 'generate_sag',
                        'node_type' => 'document',
                        'label' => 'Generate SAG',
                        'position_x' => 400,
                        'position_y' => 450,
                        'config' => ['template_id' => 'sag_renewal']
                    ],
                    [
                        'node_key' => 'payment_gateway',
                        'node_type' => 'api',
                        'label' => 'Payment Gateway',
                        'position_x' => 400,
                        'position_y' => 550,
                        'config' => ['api_endpoint' => 'payment_gateway']
                    ],
                    [
                        'node_key' => 'notification',
                        'node_type' => 'notification',
                        'label' => 'Send Notification',
                        'position_x' => 400,
                        'position_y' => 650,
                        'config' => ['channel' => 'sms']
                    ],
                    [
                        'node_key' => 'end_flow',
                        'node_type' => 'end',
                        'label' => 'End',
                        'position_x' => 400,
                        'position_y' => 750,
                        'config' => []
                    ],
                ],
                'edges' => [
                    ['source_node_key' => 'trigger_start', 'target_node_key' => 'fetch_facility', 'condition_type' => 'always'],
                    ['source_node_key' => 'fetch_facility', 'target_node_key' => 'check_overdue', 'condition_type' => 'always'],
                    ['source_node_key' => 'check_overdue', 'target_node_key' => 'tawarruq_calc', 'condition_type' => 'always'],
                    ['source_node_key' => 'tawarruq_calc', 'target_node_key' => 'generate_sag', 'condition_type' => 'always'],
                    ['source_node_key' => 'generate_sag', 'target_node_key' => 'payment_gateway', 'condition_type' => 'always'],
                    ['source_node_key' => 'payment_gateway', 'target_node_key' => 'notification', 'condition_type' => 'always'],
                    ['source_node_key' => 'notification', 'target_node_key' => 'end_flow', 'condition_type' => 'always'],
                ]
            ],
            'page_definition' => [
                'steps' => [
                    [
                        'step_key' => 'step_facility_lookup',
                        'title' => 'Facility Lookup',
                        'description' => 'Scan facility number',
                        'entity_binding' => 'facility',
                        'sort_order' => 0,
                        'fields' => [
                            [
                                'field_key' => 'facility_no',
                                'label' => 'Facility Number',
                                'component_type' => 'scanner_input',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'facility', 'target_path' => 'facility_no']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_renewal_summary',
                        'title' => 'Renewal Summary',
                        'description' => 'Review and confirm renewal',
                        'entity_binding' => 'renewal',
                        'sort_order' => 1,
                        'fields' => [
                            [
                                'field_key' => 'summary_panel',
                                'label' => 'Renewal Details',
                                'component_type' => 'summary_panel',
                                'data_type' => 'object',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => '', 'target_path' => '']
                            ],
                            [
                                'field_key' => 'signature',
                                'label' => 'Customer Signature',
                                'component_type' => 'signature_pad',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'renewal', 'target_path' => 'signature']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private static function getPledgeRedemptionBlueprint(): array
    {
        return [
            'name' => 'Pledge Redemption (Tebus Barang)',
            'domain' => 'Facility',
            'flow_definition' => [
                'nodes' => [
                    [
                        'node_key' => 'trigger_start',
                        'node_type' => 'trigger',
                        'label' => 'Redemption Request',
                        'position_x' => 400,
                        'position_y' => 50,
                        'config' => []
                    ],
                    [
                        'node_key' => 'fetch_facility',
                        'node_type' => 'command',
                        'label' => 'Fetch Facility',
                        'position_x' => 400,
                        'position_y' => 150,
                        'config' => ['command_class' => 'App\Commands\FetchFacilityCommand']
                    ],
                    [
                        'node_key' => 'tawarruq_calc_final',
                        'node_type' => 'formula',
                        'label' => 'Final Tawarruq Calc',
                        'position_x' => 400,
                        'position_y' => 250,
                        'config' => ['formula_key' => 'redemption_tawarruq_2026']
                    ],
                    [
                        'node_key' => 'payment_gateway',
                        'node_type' => 'api',
                        'label' => 'Payment Gateway',
                        'position_x' => 400,
                        'position_y' => 350,
                        'config' => ['api_endpoint' => 'payment_gateway']
                    ],
                    [
                        'node_key' => 'vault_checkout',
                        'node_type' => 'command',
                        'label' => 'Vault Check-Out',
                        'position_x' => 400,
                        'position_y' => 450,
                        'config' => ['command_class' => 'App\Commands\VaultCheckOutCommand']
                    ],
                    [
                        'node_key' => 'generate_receipt',
                        'node_type' => 'document',
                        'label' => 'Generate Receipt',
                        'position_x' => 400,
                        'position_y' => 550,
                        'config' => ['template_id' => 'redemption_receipt']
                    ],
                    [
                        'node_key' => 'notification',
                        'node_type' => 'notification',
                        'label' => 'Send Notification',
                        'position_x' => 400,
                        'position_y' => 650,
                        'config' => ['channel' => 'sms']
                    ],
                    [
                        'node_key' => 'end_flow',
                        'node_type' => 'end',
                        'label' => 'End',
                        'position_x' => 400,
                        'position_y' => 750,
                        'config' => []
                    ],
                ],
                'edges' => [
                    ['source_node_key' => 'trigger_start', 'target_node_key' => 'fetch_facility', 'condition_type' => 'always'],
                    ['source_node_key' => 'fetch_facility', 'target_node_key' => 'tawarruq_calc_final', 'condition_type' => 'always'],
                    ['source_node_key' => 'tawarruq_calc_final', 'target_node_key' => 'payment_gateway', 'condition_type' => 'always'],
                    ['source_node_key' => 'payment_gateway', 'target_node_key' => 'vault_checkout', 'condition_type' => 'always'],
                    ['source_node_key' => 'vault_checkout', 'target_node_key' => 'generate_receipt', 'condition_type' => 'always'],
                    ['source_node_key' => 'generate_receipt', 'target_node_key' => 'notification', 'condition_type' => 'always'],
                    ['source_node_key' => 'notification', 'target_node_key' => 'end_flow', 'condition_type' => 'always'],
                ]
            ],
            'page_definition' => [
                'steps' => [
                    [
                        'step_key' => 'step_facility_lookup',
                        'title' => 'Facility Lookup',
                        'description' => 'Scan facility number',
                        'entity_binding' => 'facility',
                        'sort_order' => 0,
                        'fields' => [
                            [
                                'field_key' => 'facility_no',
                                'label' => 'Facility Number',
                                'component_type' => 'scanner_input',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'facility', 'target_path' => 'facility_no']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_payment',
                        'title' => 'Payment & Redemption',
                        'description' => 'Process payment',
                        'entity_binding' => 'payment',
                        'sort_order' => 1,
                        'fields' => [
                            [
                                'field_key' => 'amount',
                                'label' => 'Payment Amount',
                                'component_type' => 'amount_input',
                                'data_type' => 'decimal',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'payment', 'target_path' => 'amount']
                            ],
                            [
                                'field_key' => 'summary',
                                'label' => 'Payment Summary',
                                'component_type' => 'summary_panel',
                                'data_type' => 'object',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => '', 'target_path' => '']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_handover',
                        'title' => 'Handover',
                        'description' => 'Item handover confirmation',
                        'entity_binding' => 'handover',
                        'sort_order' => 2,
                        'fields' => [
                            [
                                'field_key' => 'signature',
                                'label' => 'Customer Signature',
                                'component_type' => 'signature_pad',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'handover', 'target_path' => 'signature']
                            ],
                            [
                                'field_key' => 'confirm_items',
                                'label' => 'I confirm receiving all items',
                                'component_type' => 'checkbox',
                                'data_type' => 'boolean',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'handover', 'target_path' => 'confirmed']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private static function getAdditionalMarginBlueprint(): array
    {
        return [
            'name' => 'Additional Margin (Tambah Margin)',
            'domain' => 'Facility',
            'flow_definition' => [
                'nodes' => [
                    [
                        'node_key' => 'trigger_start',
                        'node_type' => 'trigger',
                        'label' => 'Additional Margin Request',
                        'position_x' => 400,
                        'position_y' => 50,
                        'config' => []
                    ],
                    [
                        'node_key' => 'fetch_facility',
                        'node_type' => 'command',
                        'label' => 'Fetch Facility',
                        'position_x' => 400,
                        'position_y' => 150,
                        'config' => ['command_class' => 'App\Commands\FetchFacilityCommand']
                    ],
                    [
                        'node_key' => 'tawarruq_calc_new_ltv',
                        'node_type' => 'formula',
                        'label' => 'Calculate New LTV',
                        'position_x' => 400,
                        'position_y' => 250,
                        'config' => ['formula_key' => 'additional_margin_ltv']
                    ],
                    [
                        'node_key' => 'check_eligible',
                        'node_type' => 'decision',
                        'label' => 'Eligible?',
                        'position_x' => 400,
                        'position_y' => 350,
                        'config' => ['condition_field' => 'ltv_ratio', 'expression' => '<= 0.80']
                    ],
                    [
                        'node_key' => 'approval',
                        'node_type' => 'approval',
                        'label' => 'Manager Approval',
                        'position_x' => 600,
                        'position_y' => 450,
                        'config' => ['role' => 'manager']
                    ],
                    [
                        'node_key' => 'generate_sag',
                        'node_type' => 'document',
                        'label' => 'Generate SAG',
                        'position_x' => 400,
                        'position_y' => 550,
                        'config' => ['template_id' => 'sag_additional_margin']
                    ],
                    [
                        'node_key' => 'payment_gateway',
                        'node_type' => 'api',
                        'label' => 'Payment Gateway',
                        'position_x' => 400,
                        'position_y' => 650,
                        'config' => ['api_endpoint' => 'payment_gateway']
                    ],
                    [
                        'node_key' => 'end_flow',
                        'node_type' => 'end',
                        'label' => 'End',
                        'position_x' => 400,
                        'position_y' => 750,
                        'config' => []
                    ],
                ],
                'edges' => [
                    ['source_node_key' => 'trigger_start', 'target_node_key' => 'fetch_facility', 'condition_type' => 'always'],
                    ['source_node_key' => 'fetch_facility', 'target_node_key' => 'tawarruq_calc_new_ltv', 'condition_type' => 'always'],
                    ['source_node_key' => 'tawarruq_calc_new_ltv', 'target_node_key' => 'check_eligible', 'condition_type' => 'always'],
                    ['source_node_key' => 'check_eligible', 'target_node_key' => 'approval', 'condition_type' => 'is_true'],
                    ['source_node_key' => 'check_eligible', 'target_node_key' => 'end_flow', 'condition_type' => 'is_false'],
                    ['source_node_key' => 'approval', 'target_node_key' => 'generate_sag', 'condition_type' => 'always'],
                    ['source_node_key' => 'generate_sag', 'target_node_key' => 'payment_gateway', 'condition_type' => 'always'],
                    ['source_node_key' => 'payment_gateway', 'target_node_key' => 'end_flow', 'condition_type' => 'always'],
                ]
            ],
            'page_definition' => [
                'steps' => [
                    [
                        'step_key' => 'step_facility_lookup',
                        'title' => 'Facility Lookup',
                        'description' => 'Scan facility number',
                        'entity_binding' => 'facility',
                        'sort_order' => 0,
                        'fields' => [
                            [
                                'field_key' => 'facility_no',
                                'label' => 'Facility Number',
                                'component_type' => 'scanner_input',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'facility', 'target_path' => 'facility_no']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_new_valuation',
                        'title' => 'New Valuation',
                        'description' => 'Capture additional gold items',
                        'entity_binding' => 'valuation',
                        'sort_order' => 1,
                        'fields' => [
                            [
                                'field_key' => 'gold_items',
                                'label' => 'Additional Gold Items',
                                'component_type' => 'gold_repeater',
                                'data_type' => 'collection',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'valuation', 'target_path' => 'items']
                            ],
                            [
                                'field_key' => 'photo',
                                'label' => 'Item Photo',
                                'component_type' => 'camera_capture',
                                'data_type' => 'file',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => 'valuation', 'target_path' => 'photo']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_summary',
                        'title' => 'Summary',
                        'description' => 'Review and confirm',
                        'entity_binding' => 'summary',
                        'sort_order' => 2,
                        'fields' => [
                            [
                                'field_key' => 'summary',
                                'label' => 'Margin Summary',
                                'component_type' => 'summary_panel',
                                'data_type' => 'object',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => '', 'target_path' => '']
                            ],
                            [
                                'field_key' => 'signature',
                                'label' => 'Customer Signature',
                                'component_type' => 'signature_pad',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'summary', 'target_path' => 'signature']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }


    private static function getMarginCallBlueprint(): array
    {
        return [
            'name' => 'Margin Call (Panggilan Margin)',
            'domain' => 'Risk',
            'flow_definition' => [
                'nodes' => [
                    [
                        'node_key' => 'trigger_start',
                        'node_type' => 'trigger',
                        'label' => 'Gold Price Alert',
                        'position_x' => 400,
                        'position_y' => 50,
                        'config' => []
                    ],
                    [
                        'node_key' => 'recalc_ltv',
                        'node_type' => 'formula',
                        'label' => 'Recalculate LTV',
                        'position_x' => 400,
                        'position_y' => 150,
                        'config' => ['formula_key' => 'margin_call_ltv']
                    ],
                    [
                        'node_key' => 'check_breach',
                        'node_type' => 'decision',
                        'label' => 'LTV Breach?',
                        'position_x' => 400,
                        'position_y' => 250,
                        'config' => ['condition_field' => 'ltv_ratio', 'expression' => '> 0.80']
                    ],
                    [
                        'node_key' => 'send_sms',
                        'node_type' => 'notification',
                        'label' => 'Send SMS Alert',
                        'position_x' => 600,
                        'position_y' => 350,
                        'config' => ['channel' => 'sms']
                    ],
                    [
                        'node_key' => 'manager_approval',
                        'node_type' => 'approval',
                        'label' => 'Manager Review',
                        'position_x' => 600,
                        'position_y' => 450,
                        'config' => ['role' => 'manager']
                    ],
                    [
                        'node_key' => 'check_response',
                        'node_type' => 'decision',
                        'label' => 'Customer Response?',
                        'position_x' => 600,
                        'position_y' => 550,
                        'config' => ['condition_field' => 'customer_responded', 'expression' => '== true']
                    ],
                    [
                        'node_key' => 'end_flow',
                        'node_type' => 'end',
                        'label' => 'End',
                        'position_x' => 400,
                        'position_y' => 650,
                        'config' => []
                    ],
                ],
                'edges' => [
                    ['source_node_key' => 'trigger_start', 'target_node_key' => 'recalc_ltv', 'condition_type' => 'always'],
                    ['source_node_key' => 'recalc_ltv', 'target_node_key' => 'check_breach', 'condition_type' => 'always'],
                    ['source_node_key' => 'check_breach', 'target_node_key' => 'send_sms', 'condition_type' => 'is_true'],
                    ['source_node_key' => 'check_breach', 'target_node_key' => 'end_flow', 'condition_type' => 'is_false'],
                    ['source_node_key' => 'send_sms', 'target_node_key' => 'manager_approval', 'condition_type' => 'always'],
                    ['source_node_key' => 'manager_approval', 'target_node_key' => 'check_response', 'condition_type' => 'always'],
                    ['source_node_key' => 'check_response', 'target_node_key' => 'end_flow', 'condition_type' => 'always'],
                ]
            ],
            'page_definition' => [
                'steps' => [
                    [
                        'step_key' => 'step_alert_review',
                        'title' => 'Alert Review',
                        'description' => 'Review margin call details',
                        'entity_binding' => 'margin_call',
                        'sort_order' => 0,
                        'fields' => [
                            [
                                'field_key' => 'summary',
                                'label' => 'Margin Call Details',
                                'component_type' => 'summary_panel',
                                'data_type' => 'object',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => '', 'target_path' => '']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_customer_action',
                        'title' => 'Customer Action',
                        'description' => 'Customer response options',
                        'entity_binding' => 'response',
                        'sort_order' => 1,
                        'fields' => [
                            [
                                'field_key' => 'action_type',
                                'label' => 'Action',
                                'component_type' => 'select',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => ['options' => ['top_up' => 'Top Up Margin', 'redeem' => 'Redeem Items', 'wait' => 'Wait']],
                                'binding' => ['target_entity' => 'response', 'target_path' => 'action_type']
                            ],
                            [
                                'field_key' => 'amount',
                                'label' => 'Amount',
                                'component_type' => 'amount_input',
                                'data_type' => 'decimal',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => 'response', 'target_path' => 'amount']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private static function getAuctionProcessBlueprint(): array
    {
        return [
            'name' => 'Auction Process (Proses Lelongan)',
            'domain' => 'Auction',
            'flow_definition' => [
                'nodes' => [
                    [
                        'node_key' => 'trigger_start',
                        'node_type' => 'trigger',
                        'label' => 'Default Trigger',
                        'position_x' => 400,
                        'position_y' => 50,
                        'config' => []
                    ],
                    [
                        'node_key' => 'generate_notice',
                        'node_type' => 'document',
                        'label' => 'Generate 14-Day Notice',
                        'position_x' => 400,
                        'position_y' => 150,
                        'config' => ['template_id' => 'auction_notice_14day']
                    ],
                    [
                        'node_key' => 'send_notification',
                        'node_type' => 'notification',
                        'label' => 'Post Notice',
                        'position_x' => 400,
                        'position_y' => 250,
                        'config' => ['channel' => 'registered_post']
                    ],
                    [
                        'node_key' => 'check_redemption',
                        'node_type' => 'decision',
                        'label' => 'Customer Redeems?',
                        'position_x' => 400,
                        'position_y' => 350,
                        'config' => ['condition_field' => 'redeemed', 'expression' => '== true']
                    ],
                    [
                        'node_key' => 'vault_checkout',
                        'node_type' => 'command',
                        'label' => 'Vault Check-Out',
                        'position_x' => 600,
                        'position_y' => 450,
                        'config' => ['command_class' => 'App\Commands\VaultCheckOutCommand']
                    ],
                    [
                        'node_key' => 'calc_reserve_price',
                        'node_type' => 'formula',
                        'label' => 'Calculate Reserve Price',
                        'position_x' => 600,
                        'position_y' => 550,
                        'config' => ['formula_key' => 'auction_reserve_price']
                    ],
                    [
                        'node_key' => 'gl_entry',
                        'node_type' => 'command',
                        'label' => 'GL Entry',
                        'position_x' => 600,
                        'position_y' => 650,
                        'config' => ['command_class' => 'App\Commands\PostGLEntryCommand']
                    ],
                    [
                        'node_key' => 'payment_surplus',
                        'node_type' => 'api',
                        'label' => 'Payment Gateway (Surplus)',
                        'position_x' => 600,
                        'position_y' => 750,
                        'config' => ['api_endpoint' => 'payment_gateway']
                    ],
                    [
                        'node_key' => 'end_flow',
                        'node_type' => 'end',
                        'label' => 'End',
                        'position_x' => 400,
                        'position_y' => 850,
                        'config' => []
                    ],
                ],
                'edges' => [
                    ['source_node_key' => 'trigger_start', 'target_node_key' => 'generate_notice', 'condition_type' => 'always'],
                    ['source_node_key' => 'generate_notice', 'target_node_key' => 'send_notification', 'condition_type' => 'always'],
                    ['source_node_key' => 'send_notification', 'target_node_key' => 'check_redemption', 'condition_type' => 'always'],
                    ['source_node_key' => 'check_redemption', 'target_node_key' => 'end_flow', 'condition_type' => 'is_true'],
                    ['source_node_key' => 'check_redemption', 'target_node_key' => 'vault_checkout', 'condition_type' => 'is_false'],
                    ['source_node_key' => 'vault_checkout', 'target_node_key' => 'calc_reserve_price', 'condition_type' => 'always'],
                    ['source_node_key' => 'calc_reserve_price', 'target_node_key' => 'gl_entry', 'condition_type' => 'always'],
                    ['source_node_key' => 'gl_entry', 'target_node_key' => 'payment_surplus', 'condition_type' => 'always'],
                    ['source_node_key' => 'payment_surplus', 'target_node_key' => 'end_flow', 'condition_type' => 'always'],
                ]
            ],
            'page_definition' => [
                'steps' => [
                    [
                        'step_key' => 'step_default_details',
                        'title' => 'Default Details',
                        'description' => 'Review default information',
                        'entity_binding' => 'default',
                        'sort_order' => 0,
                        'fields' => [
                            [
                                'field_key' => 'summary',
                                'label' => 'Default Summary',
                                'component_type' => 'summary_panel',
                                'data_type' => 'object',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => '', 'target_path' => '']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_auction_setup',
                        'title' => 'Auction Setup',
                        'description' => 'Configure auction parameters',
                        'entity_binding' => 'auction',
                        'sort_order' => 1,
                        'fields' => [
                            [
                                'field_key' => 'auction_date',
                                'label' => 'Auction Date',
                                'component_type' => 'date_picker',
                                'data_type' => 'date',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'auction', 'target_path' => 'auction_date']
                            ],
                            [
                                'field_key' => 'reserve_price',
                                'label' => 'Reserve Price',
                                'component_type' => 'amount_input',
                                'data_type' => 'decimal',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'auction', 'target_path' => 'reserve_price']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_proceeds',
                        'title' => 'Proceeds',
                        'description' => 'Auction proceeds summary',
                        'entity_binding' => 'proceeds',
                        'sort_order' => 2,
                        'fields' => [
                            [
                                'field_key' => 'proceeds_summary',
                                'label' => 'Proceeds Summary',
                                'component_type' => 'summary_panel',
                                'data_type' => 'object',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => '', 'target_path' => 'proceeds_summary']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private static function getPaymentCollectionBlueprint(): array
    {
        return [
            'name' => 'Payment Collection (Kutipan Bayaran)',
            'domain' => 'Finance',
            'flow_definition' => [
                'nodes' => [
                    [
                        'node_key' => 'trigger_start',
                        'node_type' => 'trigger',
                        'label' => 'Monthly Trigger',
                        'position_x' => 400,
                        'position_y' => 50,
                        'config' => []
                    ],
                    [
                        'node_key' => 'fetch_due',
                        'node_type' => 'command',
                        'label' => 'Fetch Due Payments',
                        'position_x' => 400,
                        'position_y' => 150,
                        'config' => ['command_class' => 'App\Commands\FetchDuePaymentsCommand']
                    ],
                    [
                        'node_key' => 'send_reminder',
                        'node_type' => 'notification',
                        'label' => 'Send Reminder',
                        'position_x' => 400,
                        'position_y' => 250,
                        'config' => ['channel' => 'sms']
                    ],
                    [
                        'node_key' => 'payment_gateway',
                        'node_type' => 'api',
                        'label' => 'Payment Gateway',
                        'position_x' => 400,
                        'position_y' => 350,
                        'config' => ['api_endpoint' => 'payment_gateway']
                    ],
                    [
                        'node_key' => 'check_received',
                        'node_type' => 'decision',
                        'label' => 'Payment Received?',
                        'position_x' => 400,
                        'position_y' => 450,
                        'config' => ['condition_field' => 'payment_received', 'expression' => '== true']
                    ],
                    [
                        'node_key' => 'gl_entry',
                        'node_type' => 'command',
                        'label' => 'GL Entry',
                        'position_x' => 600,
                        'position_y' => 550,
                        'config' => ['command_class' => 'App\Commands\PostGLEntryCommand']
                    ],
                    [
                        'node_key' => 'send_receipt',
                        'node_type' => 'notification',
                        'label' => 'Send Receipt',
                        'position_x' => 600,
                        'position_y' => 650,
                        'config' => ['channel' => 'email']
                    ],
                    [
                        'node_key' => 'end_flow',
                        'node_type' => 'end',
                        'label' => 'End',
                        'position_x' => 400,
                        'position_y' => 750,
                        'config' => []
                    ],
                ],
                'edges' => [
                    ['source_node_key' => 'trigger_start', 'target_node_key' => 'fetch_due', 'condition_type' => 'always'],
                    ['source_node_key' => 'fetch_due', 'target_node_key' => 'send_reminder', 'condition_type' => 'always'],
                    ['source_node_key' => 'send_reminder', 'target_node_key' => 'payment_gateway', 'condition_type' => 'always'],
                    ['source_node_key' => 'payment_gateway', 'target_node_key' => 'check_received', 'condition_type' => 'always'],
                    ['source_node_key' => 'check_received', 'target_node_key' => 'gl_entry', 'condition_type' => 'is_true'],
                    ['source_node_key' => 'check_received', 'target_node_key' => 'end_flow', 'condition_type' => 'is_false'],
                    ['source_node_key' => 'gl_entry', 'target_node_key' => 'send_receipt', 'condition_type' => 'always'],
                    ['source_node_key' => 'send_receipt', 'target_node_key' => 'end_flow', 'condition_type' => 'always'],
                ]
            ],
            'page_definition' => [
                'steps' => [
                    [
                        'step_key' => 'step_payment_list',
                        'title' => 'Payment List',
                        'description' => 'View due payments',
                        'entity_binding' => 'payments',
                        'sort_order' => 0,
                        'fields' => [
                            [
                                'field_key' => 'summary',
                                'label' => 'Due Payments',
                                'component_type' => 'summary_panel',
                                'data_type' => 'object',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => '', 'target_path' => '']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_payment',
                        'title' => 'Payment',
                        'description' => 'Process payment',
                        'entity_binding' => 'payment',
                        'sort_order' => 1,
                        'fields' => [
                            [
                                'field_key' => 'amount',
                                'label' => 'Payment Amount',
                                'component_type' => 'amount_input',
                                'data_type' => 'decimal',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'payment', 'target_path' => 'amount']
                            ],
                            [
                                'field_key' => 'payment_method',
                                'label' => 'Payment Method',
                                'component_type' => 'select',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => ['options' => ['cash' => 'Cash', 'card' => 'Card', 'online' => 'Online Banking']],
                                'binding' => ['target_entity' => 'payment', 'target_path' => 'method']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }


    private static function getVaultReconBlueprint(): array
    {
        return [
            'name' => 'Vault Reconciliation (Rekonsiliasi Peti Besi)',
            'domain' => 'Operations',
            'flow_definition' => [
                'nodes' => [
                    [
                        'node_key' => 'trigger_start',
                        'node_type' => 'trigger',
                        'label' => 'Daily Trigger',
                        'position_x' => 400,
                        'position_y' => 50,
                        'config' => []
                    ],
                    [
                        'node_key' => 'vault_audit',
                        'node_type' => 'command',
                        'label' => 'Vault Audit',
                        'position_x' => 400,
                        'position_y' => 150,
                        'config' => ['command_class' => 'App\Commands\VaultAuditCommand']
                    ],
                    [
                        'node_key' => 'count_vs_system',
                        'node_type' => 'formula',
                        'label' => 'Count vs System',
                        'position_x' => 400,
                        'position_y' => 250,
                        'config' => ['formula_key' => 'vault_reconciliation']
                    ],
                    [
                        'node_key' => 'check_discrepancy',
                        'node_type' => 'decision',
                        'label' => 'Discrepancy?',
                        'position_x' => 400,
                        'position_y' => 350,
                        'config' => ['condition_field' => 'has_discrepancy', 'expression' => '== true']
                    ],
                    [
                        'node_key' => 'send_alert',
                        'node_type' => 'notification',
                        'label' => 'Send Alert',
                        'position_x' => 600,
                        'position_y' => 450,
                        'config' => ['channel' => 'email']
                    ],
                    [
                        'node_key' => 'generate_report',
                        'node_type' => 'document',
                        'label' => 'Generate Report',
                        'position_x' => 400,
                        'position_y' => 550,
                        'config' => ['template_id' => 'vault_recon_report']
                    ],
                    [
                        'node_key' => 'end_flow',
                        'node_type' => 'end',
                        'label' => 'End',
                        'position_x' => 400,
                        'position_y' => 650,
                        'config' => []
                    ],
                ],
                'edges' => [
                    ['source_node_key' => 'trigger_start', 'target_node_key' => 'vault_audit', 'condition_type' => 'always'],
                    ['source_node_key' => 'vault_audit', 'target_node_key' => 'count_vs_system', 'condition_type' => 'always'],
                    ['source_node_key' => 'count_vs_system', 'target_node_key' => 'check_discrepancy', 'condition_type' => 'always'],
                    ['source_node_key' => 'check_discrepancy', 'target_node_key' => 'send_alert', 'condition_type' => 'is_true'],
                    ['source_node_key' => 'check_discrepancy', 'target_node_key' => 'generate_report', 'condition_type' => 'is_false'],
                    ['source_node_key' => 'send_alert', 'target_node_key' => 'generate_report', 'condition_type' => 'always'],
                    ['source_node_key' => 'generate_report', 'target_node_key' => 'end_flow', 'condition_type' => 'always'],
                ]
            ],
            'page_definition' => [
                'steps' => [
                    [
                        'step_key' => 'step_vault_count',
                        'title' => 'Vault Count',
                        'description' => 'Physical count of items',
                        'entity_binding' => 'vault_count',
                        'sort_order' => 0,
                        'fields' => [
                            [
                                'field_key' => 'item_barcode',
                                'label' => 'Scan Item Barcode',
                                'component_type' => 'scanner_input',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'vault_count', 'target_path' => 'barcode']
                            ],
                            [
                                'field_key' => 'notes',
                                'label' => 'Notes',
                                'component_type' => 'text_input',
                                'data_type' => 'string',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => 'vault_count', 'target_path' => 'notes']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_report',
                        'title' => 'Report',
                        'description' => 'Reconciliation report',
                        'entity_binding' => 'report',
                        'sort_order' => 1,
                        'fields' => [
                            [
                                'field_key' => 'summary',
                                'label' => 'Reconciliation Summary',
                                'component_type' => 'summary_panel',
                                'data_type' => 'object',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => '', 'target_path' => '']
                            ],
                            [
                                'field_key' => 'signature',
                                'label' => 'Auditor Signature',
                                'component_type' => 'signature_pad',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'report', 'target_path' => 'signature']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private static function getKycUpdateBlueprint(): array
    {
        return [
            'name' => 'Customer KYC Update (Kemas Kini Profil)',
            'domain' => 'Customer',
            'flow_definition' => [
                'nodes' => [
                    [
                        'node_key' => 'trigger_start',
                        'node_type' => 'trigger',
                        'label' => 'Manual Trigger',
                        'position_x' => 400,
                        'position_y' => 50,
                        'config' => []
                    ],
                    [
                        'node_key' => 'fetch_profile',
                        'node_type' => 'command',
                        'label' => 'Fetch Customer Profile',
                        'position_x' => 400,
                        'position_y' => 150,
                        'config' => ['command_class' => 'App\Commands\FetchCustomerCommand']
                    ],
                    [
                        'node_key' => 'amla_check',
                        'node_type' => 'api',
                        'label' => 'AMLA Check',
                        'position_x' => 400,
                        'position_y' => 250,
                        'config' => ['api_endpoint' => 'amla_api']
                    ],
                    [
                        'node_key' => 'check_flagged',
                        'node_type' => 'decision',
                        'label' => 'Flagged?',
                        'position_x' => 400,
                        'position_y' => 350,
                        'config' => ['condition_field' => 'is_flagged', 'expression' => '== true']
                    ],
                    [
                        'node_key' => 'compliance_approval',
                        'node_type' => 'approval',
                        'label' => 'Compliance Approval',
                        'position_x' => 600,
                        'position_y' => 450,
                        'config' => ['role' => 'compliance_officer']
                    ],
                    [
                        'node_key' => 'update_profile',
                        'node_type' => 'command',
                        'label' => 'Update Profile',
                        'position_x' => 400,
                        'position_y' => 550,
                        'config' => ['command_class' => 'App\Commands\UpdateCustomerCommand']
                    ],
                    [
                        'node_key' => 'end_flow',
                        'node_type' => 'end',
                        'label' => 'End',
                        'position_x' => 400,
                        'position_y' => 650,
                        'config' => []
                    ],
                ],
                'edges' => [
                    ['source_node_key' => 'trigger_start', 'target_node_key' => 'fetch_profile', 'condition_type' => 'always'],
                    ['source_node_key' => 'fetch_profile', 'target_node_key' => 'amla_check', 'condition_type' => 'always'],
                    ['source_node_key' => 'amla_check', 'target_node_key' => 'check_flagged', 'condition_type' => 'always'],
                    ['source_node_key' => 'check_flagged', 'target_node_key' => 'compliance_approval', 'condition_type' => 'is_true'],
                    ['source_node_key' => 'check_flagged', 'target_node_key' => 'update_profile', 'condition_type' => 'is_false'],
                    ['source_node_key' => 'compliance_approval', 'target_node_key' => 'update_profile', 'condition_type' => 'always'],
                    ['source_node_key' => 'update_profile', 'target_node_key' => 'end_flow', 'condition_type' => 'always'],
                ]
            ],
            'page_definition' => [
                'steps' => [
                    [
                        'step_key' => 'step_customer',
                        'title' => 'Customer Information',
                        'description' => 'Update customer details',
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
                                'field_key' => 'ic_scan',
                                'label' => 'Scan IC',
                                'component_type' => 'scanner_input',
                                'data_type' => 'string',
                                'is_required' => false,
                                'config' => [],
                                'binding' => ['target_entity' => 'customer', 'target_path' => 'ic_scan']
                            ],
                            [
                                'field_key' => 'full_name',
                                'label' => 'Full Name',
                                'component_type' => 'text_input',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'customer', 'target_path' => 'name']
                            ],
                            [
                                'field_key' => 'phone',
                                'label' => 'Phone Number',
                                'component_type' => 'phone_input',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'customer', 'target_path' => 'phone']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_consent',
                        'title' => 'Consent',
                        'description' => 'Customer consent and signature',
                        'entity_binding' => 'consent',
                        'sort_order' => 1,
                        'fields' => [
                            [
                                'field_key' => 'consent_agree',
                                'label' => 'I agree to the terms and conditions',
                                'component_type' => 'checkbox',
                                'data_type' => 'boolean',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'consent', 'target_path' => 'agreed']
                            ],
                            [
                                'field_key' => 'signature',
                                'label' => 'Customer Signature',
                                'component_type' => 'signature_pad',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'consent', 'target_path' => 'signature']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private static function getBnmReportBlueprint(): array
    {
        return [
            'name' => 'BNM Compliance Report (Laporan Pematuhan)',
            'domain' => 'Compliance',
            'flow_definition' => [
                'nodes' => [
                    [
                        'node_key' => 'trigger_start',
                        'node_type' => 'trigger',
                        'label' => 'Monthly Trigger',
                        'position_x' => 400,
                        'position_y' => 50,
                        'config' => []
                    ],
                    [
                        'node_key' => 'aggregate_stats',
                        'node_type' => 'formula',
                        'label' => 'Aggregate Statistics',
                        'position_x' => 400,
                        'position_y' => 150,
                        'config' => ['formula_key' => 'bnm_stats_aggregation']
                    ],
                    [
                        'node_key' => 'amla_alerts',
                        'node_type' => 'api',
                        'label' => 'Fetch AMLA Alerts',
                        'position_x' => 400,
                        'position_y' => 250,
                        'config' => ['api_endpoint' => 'amla_api']
                    ],
                    [
                        'node_key' => 'generate_report',
                        'node_type' => 'document',
                        'label' => 'Generate BNM Report',
                        'position_x' => 400,
                        'position_y' => 350,
                        'config' => ['template_id' => 'bnm_compliance_report']
                    ],
                    [
                        'node_key' => 'send_email',
                        'node_type' => 'notification',
                        'label' => 'Email Report',
                        'position_x' => 400,
                        'position_y' => 450,
                        'config' => ['channel' => 'email']
                    ],
                    [
                        'node_key' => 'end_flow',
                        'node_type' => 'end',
                        'label' => 'End',
                        'position_x' => 400,
                        'position_y' => 550,
                        'config' => []
                    ],
                ],
                'edges' => [
                    ['source_node_key' => 'trigger_start', 'target_node_key' => 'aggregate_stats', 'condition_type' => 'always'],
                    ['source_node_key' => 'aggregate_stats', 'target_node_key' => 'amla_alerts', 'condition_type' => 'always'],
                    ['source_node_key' => 'amla_alerts', 'target_node_key' => 'generate_report', 'condition_type' => 'always'],
                    ['source_node_key' => 'generate_report', 'target_node_key' => 'send_email', 'condition_type' => 'always'],
                    ['source_node_key' => 'send_email', 'target_node_key' => 'end_flow', 'condition_type' => 'always'],
                ]
            ],
            'page_definition' => [
                'steps' => [
                    [
                        'step_key' => 'step_parameters',
                        'title' => 'Report Parameters',
                        'description' => 'Configure report settings',
                        'entity_binding' => 'report_params',
                        'sort_order' => 0,
                        'fields' => [
                            [
                                'field_key' => 'report_period',
                                'label' => 'Report Period',
                                'component_type' => 'date_picker',
                                'data_type' => 'date',
                                'is_required' => true,
                                'config' => [],
                                'binding' => ['target_entity' => 'report_params', 'target_path' => 'period']
                            ],
                            [
                                'field_key' => 'report_type',
                                'label' => 'Report Type',
                                'component_type' => 'select',
                                'data_type' => 'string',
                                'is_required' => true,
                                'config' => ['options' => ['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annual' => 'Annual']],
                                'binding' => ['target_entity' => 'report_params', 'target_path' => 'type']
                            ]
                        ]
                    ],
                    [
                        'step_key' => 'step_preview',
                        'title' => 'Preview',
                        'description' => 'Review report before submission',
                        'entity_binding' => 'preview',
                        'sort_order' => 1,
                        'fields' => [
                            [
                                'field_key' => 'summary',
                                'label' => 'Report Preview',
                                'component_type' => 'summary_panel',
                                'data_type' => 'object',
                                'is_required' => false,
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
