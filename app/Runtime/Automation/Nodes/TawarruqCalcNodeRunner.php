<?php

namespace App\Runtime\Automation\Nodes;

use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;
use Exception;

class TawarruqCalcNodeRunner implements NodeRunner
{
    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $config = $node->config ?? [];
        
        // Input variables path or explicit values
        $marhunValuePath = $config['marhun_value'] ?? null;
        $marginRatePath = $config['margin_rate'] ?? null; // e.g., 2.5% per 6 months
        $ltvRatioPath = $config['ltv_ratio'] ?? null; // e.g., 70%
        $ujrahRatePath = $config['ujrah_rate'] ?? null; // e.g., 0.75 per 100 per month
        $tenureMonthsPath = $config['tenure_months'] ?? null; // e.g., 6

        if (!$marhunValuePath) {
            throw new Exception("TawarruqCalcNodeRunner: marhun_value is required.");
        }

        $marhunValue = (float) $context->get($marhunValuePath, 0);
        $marginRate = (float) $context->get($marginRatePath, 0.025); // 2.5%
        $ltvRatio = (float) $context->get($ltvRatioPath, 0.70); // 70%
        $ujrahRate = (float) $context->get($ujrahRatePath, 0.75); // 0.75 per 100
        $tenureMonths = (int) $context->get($tenureMonthsPath, 6);

        // Calculate Commodity Cost (Financing Amount)
        $commodityCost = $marhunValue * $ltvRatio;

        // Calculate Murabahah Sale Price
        $murabahahProfit = $commodityCost * $marginRate;
        $murabahahSalePrice = $commodityCost + $murabahahProfit;

        // Calculate Ujrah (Safekeeping Fee)
        // Rate is per RM100 of marhun value per month
        $monthlyUjrah = ($marhunValue / 100) * $ujrahRate;
        $totalUjrah = $monthlyUjrah * $tenureMonths;

        // Calculate Total Redemption Amount
        $totalRedemption = $murabahahSalePrice + $totalUjrah;

        $results = [
            'marhun_value' => $marhunValue,
            'commodity_cost' => $commodityCost,
            'cash_received' => $commodityCost, // Wakalah sale proceeds
            'murabahah_sale_price' => $murabahahSalePrice,
            'profit_margin_amount' => $murabahahProfit,
            'monthly_ujrah' => $monthlyUjrah,
            'total_ujrah' => $totalUjrah,
            'total_redemption' => $totalRedemption,
            'tenure_months' => $tenureMonths
        ];

        // Store results in context under a defined output key or default
        $outputKey = $config['output_key'] ?? 'tawarruq';
        $context->set($outputKey, $results);

        return $results;
    }
}
