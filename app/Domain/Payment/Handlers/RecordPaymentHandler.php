<?php

namespace App\Domain\Payment\Handlers;

use App\Domain\Payment\Commands\RecordPayment;
use App\Domain\Payment\Events\PaymentRecorded;
use App\Domain\Payment\Models\PaymentTransaction;
use App\Kernel\Contracts\CommandHandler;
use App\Kernel\Contracts\Command;

class RecordPaymentHandler implements CommandHandler
{
    /**
     * @param RecordPayment $command
     */
    public function handle(Command $command): PaymentTransaction
    {
        $transaction = PaymentTransaction::create([
            'facility_id' => $command->facilityId,
            'payment_type' => $command->paymentType,
            'amount' => $command->amount,
            'payment_method' => $command->paymentMethod,
            'reference_number' => $command->referenceNumber,
            'received_by' => auth()->id(),
            'branch_id' => $command->branchId,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        event(new PaymentRecorded($transaction));

        return $transaction;
    }
}
