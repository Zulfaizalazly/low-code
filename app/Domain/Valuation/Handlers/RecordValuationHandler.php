<?php

namespace App\Domain\Valuation\Handlers;

use App\Domain\Valuation\Commands\RecordValuation;
use App\Domain\Valuation\Events\ValuationRecorded;
use App\Domain\Valuation\Models\Valuation;
use App\Kernel\Contracts\CommandHandler;
use App\Kernel\Contracts\Command;

class RecordValuationHandler implements CommandHandler
{
    /**
     * @param RecordValuation $command
     */
    public function handle(Command $command): Valuation
    {
        $grossValue = ($command->goldPricePerGram * $command->weightGrams * ($command->purityPercentage / 100));
        $valuationAmount = $grossValue * ($command->ltvPercentage / 100);

        $valuation = Valuation::create([
            'facility_id' => $command->facilityId,
            'facility_item_id' => $command->facilityItemId,
            'gold_price_per_gram' => $command->goldPricePerGram,
            'weight_grams' => $command->weightGrams,
            'purity_percentage' => $command->purityPercentage,
            'gross_value' => $grossValue,
            'ltv_percentage' => $command->ltvPercentage,
            'valuation_amount' => $valuationAmount,
            'valued_by' => $command->valuedBy ?? auth()->id(),
            'valued_at' => now(),
        ]);

        event(new ValuationRecorded($valuation));

        return $valuation;
    }
}
