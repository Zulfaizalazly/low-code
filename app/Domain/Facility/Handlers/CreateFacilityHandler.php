<?php

namespace App\Domain\Facility\Handlers;

use App\Domain\Facility\Commands\CreateFacility;
use App\Domain\Facility\Events\FacilityCreated;
use App\Domain\Facility\Models\Facility;
use App\Kernel\Contracts\CommandHandler;
use App\Kernel\Contracts\Command;
use Illuminate\Support\Str;

class CreateFacilityHandler implements CommandHandler
{
    /**
     * @param CreateFacility $command
     */
    public function handle(Command $command): Facility
    {
        $facility = Facility::create([
            'customer_id' => $command->customerId,
            'product_code' => $command->productCode,
            'branch_id' => $command->branchId,
            'entity_id' => $command->entityId,
            'facility_number' => 'FAC-' . strtoupper(Str::random(8)),
            'principal_amount' => $command->principalAmount,
            'tenure_months' => $command->tenureMonths,
            'profit_rate' => $command->profitRate,
            'status' => 'draft',
        ]);

        // Create Items
        foreach ($command->items as $itemData) {
            $facility->items()->create($itemData);
        }

        // Create Nominees
        foreach ($command->nominees as $nomineeData) {
            $facility->nominees()->create($nomineeData);
        }

        event(new FacilityCreated($facility));

        return $facility;
    }
}
