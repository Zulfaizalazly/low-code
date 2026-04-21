<?php

namespace App\Http\Controllers\Api\Studio;

use App\Http\Controllers\Controller;
use App\Studio\Registry\PageDefinition;
use App\Studio\Validation\PageValidator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PageBuilderController extends Controller
{
    public function save(Request $request, int $pageId): JsonResponse
    {
        $page = PageDefinition::findOrFail($pageId);
        $steps = $request->input('steps', []);

        DB::transaction(function () use ($page, $steps) {
            // Delete existing steps (cascades to fields and bindings usually, 
            // but let's be explicit if needed byregistry definition)
            $page->steps()->each(function($step) {
                $step->fields()->each(fn($f) => $f->binding()->delete());
                $step->fields()->delete();
                $step->delete();
            });

            foreach ($steps as $sIdx => $sData) {
                $step = $page->steps()->create([
                    'title' => $sData['title'],
                    'step_key' => $sData['step_key'] ?? 'step_'.($sIdx+1),
                    'sort_order' => $sData['sort_order'] ?? $sData['order'] ?? $sIdx,
                    'config' => $sData['config'] ?? [],
                ]);

                foreach ($sData['fields'] ?? [] as $fIdx => $fData) {
                    $field = $step->fields()->create([
                        'field_key' => $fData['field_key'],
                        'label' => $fData['label'],
                        'component_type' => $fData['component_type'],
                        'sort_order' => $fData['sort_order'] ?? $fData['order'] ?? $fIdx,
                        'is_required' => $fData['is_required'] ?? false,
                        'config' => $fData['config'] ?? [],
                        'default_value' => $fData['default_value'] ?? null,
                    ]);

                    if (!empty($fData['binding'])) {
                        $field->binding()->create([
                            'binding_type' => $fData['binding']['binding_type'] ?? 'entity',
                            'target_entity' => $fData['binding']['target_entity'] ?? null,
                            'target_path' => $fData['binding']['target_path'] ?? null,
                            'write_mode' => $fData['binding']['write_mode'] ?? 'create',
                            'read_mode' => $fData['binding']['read_mode'] ?? ($fData['binding']['mapping_type'] ?? 'direct'),
                            'transformer_key' => $fData['binding']['transformer_key'] ?? null,
                        ]);
                    }
                }
            }
        });

        return response()->json(['message' => 'Page saved successfully']);
    }

    public function validate(Request $request, int $pageId): JsonResponse
    {
        $page = PageDefinition::findOrFail($pageId);
        $validator = new PageValidator();
        $result = $validator->validate($page);

        return response()->json($result);
    }

    public function entities(): JsonResponse
    {
        // Mocking available entities for now, should eventually come from a SchemaRegistry
        return response()->json([
            'entities' => [
                'customers' => ['id', 'name', 'ic_number', 'phone', 'email', 'address'],
                'facilities' => ['id', 'facility_no', 'principal_amount', 'tenure', 'margin', 'status'],
                'facility_items' => ['id', 'facility_id', 'gold_type', 'weight_gross', 'weight_net', 'value_market'],
                'facility_nominees' => ['id', 'facility_id', 'name', 'ic_number', 'relationship'],
                'valuations' => ['id', 'valuation_no', 'total_gold_value', 'total_loan_eligible'],
                'approval_tasks' => ['id', 'task_type', 'status', 'assigned_role'],
                'payment_transactions' => ['id', 'transaction_no', 'amount', 'payment_method'],
            ]
        ]);
    }
}
