<?php

namespace App\Domain\Compliance\Handlers;

use App\Domain\Compliance\Commands\AmlaCheck;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AmlaCheckHandler
{
    /**
     * Handle the AMLA Check command.
     * 
     * In a real system, this would call an external API (e.g., world-check, sanction lists).
     */
    public function handle(AmlaCheck $command): array
    {
        Log::info("Executing AMLA Check for IC: {$command->icNumber}", [
            'name' => $command->name,
            'amount' => $command->amount
        ]);

        // --- Demo Logic: Mocking an External API Call ---
        // $response = Http::withToken(config('services.amla.key'))
        //     ->post('https://api.compliance-provider.com/v1/check', [
        //         'ic' => $command->icNumber,
        //         'name' => $command->name
        //     ]);

        // Simple mock check: Any IC ending in '999' is flagged as "High Risk"
        $isFlagged = str_ends_with($command->icNumber, '999');
        
        if ($isFlagged) {
            return [
                'status' => 'flagged',
                'risk_level' => 'high',
                'reason' => 'Matched known PEP (Politically Exposed Person) list',
                'timestamp' => now()->toIso8601String()
            ];
        }

        return [
            'status' => 'cleared',
            'risk_level' => 'low',
            'score' => 0.02,
            'message' => 'No matches found in standard sanction lists.',
            'timestamp' => now()->toIso8601String()
        ];
    }
}
