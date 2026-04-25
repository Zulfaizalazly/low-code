<?php

namespace App\Studio\AI;

class FieldHintPromptBuilder
{
    /**
     * Build the system prompt with Ar-Rahnu domain context.
     */
    public function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an AI assistant for an Ar-Rahnu (Islamic pawnbroking) flow builder application.

The application allows users to build automated workflows using the following node types:
- trigger: Flow entry point that starts the workflow execution
- command: Execute business logic operations
- decision: Conditional branching based on data or rules
- approval: Human approval workflow requiring authorized sign-off
- notification: Send alerts via email, SMS, or WhatsApp
- document: Generate documents like Surat Pajak (pawn ticket)
- gl_action: Post general ledger entries for accounting
- formula: Calculate values like margin, ujrah (service charge), and other financial computations
- payment_gateway: Process payments via Billplz, Bayarcash, ToyyibPay, Stripe, Chip, and others
- vault_action: Manage physical gold storage in secure vaults
- api_request: Call external APIs for integrations
- tawarruq_calc: Islamic financing (tawarruq) calculation
- generate_pdf: PDF generation for reports and documents

Respond in English. Provide practical examples relevant to the Ar-Rahnu domain.
PROMPT;
    }

    /**
     * Build the user prompt based on field context and mode.
     */
    public function buildUserPrompt(
        string $nodeType,
        string $fieldKey,
        string $fieldLabel,
        string $mode,
        ?string $userQuestion = null
    ): string {
        if ($mode === 'quick') {
            $prompt = "Explain the field '{$fieldLabel}' (key: {$fieldKey}) in a {$nodeType} node. Respond in 20-50 tokens. Include: purpose, expected value type, one brief example.";
        } else {
            $prompt = "Provide a comprehensive explanation of the field '{$fieldLabel}' (key: {$fieldKey}) in a {$nodeType} node. Include: purpose, expected value format, common values, how it connects to other nodes, and practical Ar-Rahnu examples.";
        }

        if ($userQuestion !== null) {
            $prompt .= "\n\nThe user asks: {$userQuestion}";
        }

        if ($nodeType === 'payment_gateway' && str_starts_with($fieldKey, 'credentials.')) {
            $prompt .= "\n\nThis field relates to payment provider credentials. Supported providers: Billplz (collection_id, api_key), Bayarcash (portal_key, pat, secret_key), ToyyibPay (category_code, user_secret_key), Stripe (secret_key), Chip (brand_id, api_key). Explain how to obtain this credential from the provider's dashboard.";
        }

        return $prompt;
    }
}
