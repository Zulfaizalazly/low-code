<?php

namespace App\Services;

class FormulaRegistry
{
    public static function getFormula(string $key): ?array
    {
        $formulas = [
            'standard_margin_2026' => [
                'formula' => '(weight * purity_factor) * gold_price * 0.70',
                'variables' => [
                    'weight' => 'form.weight', // Mock path
                    'purity_factor' => 'form.purity', // Mock path
                    'gold_price' => 'nodes.fetch_gold_price.output.body.price_gram_24k'
                ],
                'output_key' => 'margin_amount'
            ],
            'additional_margin_ltv' => [
                'formula' => 'total_loan / current_value',
                'variables' => [
                    'total_loan' => 'nodes.fetch_facility.output.total_loan',
                    'current_value' => 'form.new_valuation'
                ],
                'output_key' => 'ltv_ratio'
            ]
        ];

        return $formulas[$key] ?? null;
    }
}
