<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class OcrIntegrationService
{
    /**
     * Parse MyKad details from an image using OCR.space or similar.
     */
    public function parseMyKad(string $imageBase64): array
    {
        // For Demo/Simulation: Return mock data based on "magic" base64 strings
        if (str_contains($imageBase64, 'demo_mykad_ali')) {
            return [
                'name' => 'MUHAMMAD ALI BIN ABDULLAH',
                'ic_number' => '900101-14-5567',
                'address' => 'NO 12, JALAN SERI PUTRA 1, BANDAR SERI PUTRA, 43000 KAJANG, SELANGOR',
                'gender' => 'Male',
                'success' => true
            ];
        }

        // Real API Call (Mocked for now)
        try {
            // $response = Http::post('https://api.ocr.space/parse/image', [
            //     'apikey' => config('services.ocr_space.key'),
            //     'base64Image' => $imageBase64,
            //     'OCREngine' => 2
            // ]);
            
            return [
                'success' => false,
                'message' => 'OCR Service is in Simulation Mode.'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
