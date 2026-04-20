<?php

namespace App\Domain\Accounting\Handlers;

use App\Domain\Accounting\Commands\PostJournalEntry;
use App\Domain\Accounting\Events\JournalEntryPosted;
use App\Domain\Accounting\Models\JournalEntry;
use App\Kernel\Contracts\CommandHandler;
use App\Kernel\Contracts\Command;
use Exception;
use Illuminate\Support\Str;

class PostJournalEntryHandler implements CommandHandler
{
    /**
     * @param PostJournalEntry $command
     */
    public function handle(Command $command): JournalEntry
    {
        $totalDebit = collect($command->lines)->sum('debit');
        $totalCredit = collect($command->lines)->sum('credit');

        // Enforcement of double-entry accounting rule
        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw new Exception("Journal entry is not balanced. Debit: {$totalDebit}, Credit: {$totalCredit}");
        }

        $entry = JournalEntry::create([
            'entry_number' => 'JE-' . strtoupper(Str::random(10)),
            'description' => $command->description,
            'reference_type' => $command->referenceType,
            'reference_id' => $command->referenceId,
            'posted_by' => auth()->id(),
            'posted_at' => now(),
            'is_balanced' => true,
        ]);

        foreach ($command->lines as $lineData) {
            $entry->lines()->create([
                'account_code' => $lineData['account_code'],
                'account_name' => $lineData['account_name'],
                'debit_amount' => $lineData['debit'],
                'credit_amount' => $lineData['credit'],
                'description' => $lineData['description'] ?? $command->description,
            ]);
        }

        event(new JournalEntryPosted($entry));

        return $entry;
    }
}
