/**
 * useMockDataGenerator — Generates sensible demo/mock values for flow node config fields.
 *
 * Used by the "AI Mock Data Auto-Fill" button in the Preflight Check modal.
 * Produces deterministic, domain-aware mock data for Ar-Rahnu pawnbroking
 * operations so that blueprints can run end-to-end in simulation mode.
 *
 * No AI API calls needed — all values are generated client-side based on
 * node type, field key, and available options.
 */

import type { ConfigFieldDef } from './useConfigFields'

interface MockContext {
  nodeType: string
  nodeLabel: string
  nodeKey: string
  commands: any[]
}

/**
 * Generate a mock value for a single config field.
 */
export function generateMockValue(
  field: ConfigFieldDef,
  ctx: MockContext
): string | number | object | null {
  // Skip dividers
  if (field.type === 'divider') return null

  // Select fields — pick first option
  if (field.type === 'select') {
    if (field.key === 'command_class' && Array.isArray(field.options)) {
      // Pick a command that matches the node label context
      const cmd = pickCommandByContext(field.options as any[], ctx.nodeLabel)
      return cmd?.class ?? (field.options[0] as any)?.class ?? ''
    }
    if (Array.isArray(field.options) && field.options.length > 0) {
      if (typeof field.options[0] === 'string') {
        return pickSelectByContext(field.options as string[], field.key, ctx)
      }
    }
    return ''
  }

  // Number fields
  if (field.type === 'number') {
    return getNumberMock(field.key, ctx)
  }

  // JSON fields
  if (field.type === 'json') {
    return getJsonMock(field.key, ctx)
  }

  // Text / textarea fields
  return getTextMock(field.key, ctx)
}

/**
 * Generate mock values for ALL missing fields of a failed node.
 */
export function generateMockForNode(
  missingFields: ConfigFieldDef[],
  ctx: MockContext
): Record<string, any> {
  const result: Record<string, any> = {}
  for (const field of missingFields) {
    const value = generateMockValue(field, ctx)
    if (value !== null) {
      result[field.key] = value
    }
  }
  return result
}

// ─── Helpers ────────────────────────────────────────────────

function pickCommandByContext(commands: any[], nodeLabel: string): any {
  const label = nodeLabel.toLowerCase()
  const mappings: [string[], string][] = [
    [['customer', 'register', 'kyc', 'profil'], 'RegisterCustomer'],
    [['facility', 'pledge', 'gadai', 'pajak'], 'CreateFacility'],
    [['payment', 'bayar', 'kutip'], 'RecordPayment'],
    [['valuation', 'nilai', 'taksir'], 'RecordValuation'],
    [['notification', 'notis', 'maklum'], 'SendNotification'],
    [['document', 'surat', 'dokumen'], 'GenerateDocument'],
    [['journal', 'gl', 'akaun'], 'PostJournalEntry'],
    [['approval', 'lulus', 'kelulusan'], 'CreateApprovalTask'],
    [['amla', 'compliance', 'pematuhan'], 'AmlaCheck'],
    [['vault', 'peti', 'simpan'], 'VaultCheckIn'],
    [['fetch', 'ambil'], 'FetchCustomerCommand'],
  ]

  for (const [keywords, className] of mappings) {
    if (keywords.some(k => label.includes(k))) {
      const found = commands.find(c => c.class?.includes(className) || c.name?.toLowerCase().includes(className.toLowerCase()))
      if (found) return found
    }
  }
  return commands[0] ?? null
}

function pickSelectByContext(options: string[], fieldKey: string, ctx: MockContext): string {
  const key = fieldKey.toLowerCase()
  const label = ctx.nodeLabel.toLowerCase()

  // Trigger type
  if (key === 'trigger_type') return 'manual_start'

  // Channel
  if (key === 'channel') {
    if (label.includes('sms') || label.includes('telefon')) return 'sms'
    if (label.includes('whatsapp')) return 'whatsapp'
    if (label.includes('inbox') || label.includes('dalaman')) return 'internal_inbox'
    return 'email'
  }

  // Condition type
  if (key === 'condition_type') return 'expression'

  // Output format
  if (key === 'output_format') return 'pdf'

  // HTTP method
  if (key === 'method') return 'POST'

  // Auth type
  if (key === 'auth_type') return 'bearer'

  // Payment provider
  if (key === 'provider') return 'billplz'

  // Transaction type
  if (key === 'type') return 'collection'

  // Vault action
  if (key === 'action') {
    if (label.includes('check out') || label.includes('keluar')) return 'check_out'
    if (label.includes('audit')) return 'audit'
    return 'check_in'
  }

  // Approval
  if (key === 'assigned_role') return 'branch_manager'
  if (key === 'approval_tier') return 'tier_1'

  // Default: first option
  return options[0] ?? ''
}

function getNumberMock(fieldKey: string, ctx: MockContext): number {
  if (fieldKey === 'sla_hours') return 24
  if (fieldKey.includes('rate')) return 2.5
  if (fieldKey.includes('ratio')) return 0.7
  if (fieldKey.includes('month')) return 6
  return 1
}

function getJsonMock(fieldKey: string, ctx: MockContext): string {
  const label = ctx.nodeLabel.toLowerCase()

  if (fieldKey === 'input_mapping') {
    // Formula input mapping
    if (label.includes('reserve') || label.includes('price')) {
      return JSON.stringify({ principal: '$.form.amount', rate: '$.margin_rate', weight: '$.form.weight_grams' }, null, 2)
    }
    if (label.includes('ujrah') || label.includes('profit')) {
      return JSON.stringify({ principal: '$.facility.principal_amount', rate: '0.75', tenure: '6' }, null, 2)
    }
    return JSON.stringify({ value: '$.form.amount' }, null, 2)
  }

  if (fieldKey === 'headers') {
    return JSON.stringify({ 'Content-Type': 'application/json', 'Accept': 'application/json' }, null, 2)
  }

  if (fieldKey === 'payload') {
    return JSON.stringify({ customer_id: '$.customer.id', amount: '$.form.amount' }, null, 2)
  }

  return '{}'
}

function getTextMock(fieldKey: string, ctx: MockContext): string {
  const key = fieldKey.toLowerCase()
  const label = ctx.nodeLabel.toLowerCase()

  // ─── Context paths ($.xxx patterns) ───
  if (key === 'recipient') {
    if (label.includes('sms') || label.includes('telefon')) return '$.customer.phone'
    return '$.customer.email'
  }
  if (key === 'amount_path' || key === 'amount') return '$.facility.principal_amount'
  if (key === 'marhun_id') return '$.facility.marhun_id'
  if (key === 'attach_to_entity') return '$.facility.id'
  if (key === 'result_path') return '$.calculated_value'
  if (key === 'output_key') return ctx.nodeKey + '_result'
  if (key === 'marhun_value') return '$.facility.marhun_value'
  if (key === 'margin_rate') return '$.product.margin_rate'
  if (key === 'ltv_ratio') return '$.product.ltv_ratio'
  if (key === 'ujrah_rate') return '$.product.ujrah_rate'
  if (key === 'tenure_months') return '$.facility.tenure_months'

  // ─── Template keys ───
  if (key === 'template_key') {
    if (label.includes('gadai') || label.includes('pledge') || label.includes('surat')) return 'surat_gadai_v1'
    if (label.includes('renewal') || label.includes('sambung')) return 'sag_renewal'
    if (label.includes('redemption') || label.includes('tebus')) return 'surat_tebus_v1'
    if (label.includes('notice') || label.includes('notis')) return 'notis_14hari_v1'
    if (label.includes('receipt') || label.includes('resit')) return 'resit_bayaran_v1'
    return 'template_default_v1'
  }
  if (key === 'template_id') {
    if (label.includes('gadai') || label.includes('pledge')) return 'sag_standard_v1'
    if (label.includes('notice') || label.includes('notis')) return 'notis_14hari_pdf_v1'
    return 'doc_standard_v1'
  }

  // ─── Transaction codes ───
  if (key === 'transaction_code') {
    if (label.includes('disburs') || label.includes('keluar')) return 'GL-DISB-001'
    if (label.includes('collect') || label.includes('kutip')) return 'GL-COLL-001'
    if (label.includes('ujrah') || label.includes('profit')) return 'GL-UJRAH-001'
    return 'GL-TXN-001'
  }

  // ─── Narration ───
  if (key === 'narration') {
    if (label.includes('disburs')) return 'Disbursement for facility #$.facility.id'
    if (label.includes('collect')) return 'Payment collection for facility #$.facility.id'
    return 'Transaction for facility #$.facility.id'
  }

  // ─── Subject ───
  if (key === 'subject') {
    if (label.includes('notice') || label.includes('notis')) return 'Notis Penting: Pajakan Anda #$.facility.id'
    if (label.includes('receipt') || label.includes('resit')) return 'Resit Bayaran #$.payment.reference'
    return 'Makluman Ar-Rahnu: $.facility.id'
  }

  // ─── Expression (decision nodes) ───
  if (key === 'expression') {
    if (label.includes('redeem') || label.includes('tebus')) return '$.customer_decision === "redeem"'
    if (label.includes('margin') || label.includes('call')) return '$.margin_ratio < 0.7'
    if (label.includes('amount') || label.includes('jumlah')) return '$.form.amount > 10000'
    if (label.includes('approve') || label.includes('lulus')) return '$.approval.status === "approved"'
    return '$.result === true'
  }

  // ─── Event name ───
  if (key === 'event_name') return 'facility.created'

  // ─── Formula key ───
  if (key === 'formula_key') {
    if (label.includes('reserve') || label.includes('price')) return 'calculate_reserve_price'
    if (label.includes('ujrah') || label.includes('profit')) return 'calculate_ujrah'
    if (label.includes('margin')) return 'calculate_margin'
    return 'calculate_value'
  }

  // ─── API fields ───
  if (key === 'url') return 'https://api.demo.arrahnu.com/v1/verify'
  if (key === 'auth_token') return 'demo_bearer_token_mock_12345'
  if (key === 'auth_username') return 'demo_user'
  if (key === 'auth_password') return 'demo_pass_mock'

  // ─── Payment credentials (mock) ───
  if (key.startsWith('credentials.')) {
    const credKey = key.replace('credentials.', '')
    if (credKey === 'collection_id') return 'demo_collection_001'
    if (credKey === 'api_key') return 'demo_api_key_mock_12345'
    if (credKey === 'secret_key') return 'demo_secret_key_mock_12345'
    if (credKey === 'category_code') return 'demo_cat_001'
    if (credKey === 'user_secret_key') return 'demo_user_secret_mock'
    if (credKey === 'portal_key') return 'demo_portal_key_mock'
    if (credKey === 'pat') return 'demo_pat_mock_12345'
    if (credKey === 'brand_id') return 'demo_brand_001'
    return 'demo_mock_value'
  }

  // ─── Command argument mapping ───
  if (key.startsWith('mapping.')) {
    const argName = key.replace('mapping.', '')
    return guessContextPath(argName, ctx)
  }

  // ─── Fallback ───
  return 'demo_value'
}

/**
 * Guess a context path for a command argument based on its name.
 */
function guessContextPath(argName: string, ctx: MockContext): string {
  const name = argName.toLowerCase()

  // Customer fields
  if (name === 'name' || name === 'fullname' || name === 'full_name') return '$.form.name'
  if (name === 'icnumber' || name === 'ic_number' || name === 'ic') return '$.form.ic_number'
  if (name === 'email') return '$.form.email'
  if (name === 'phone' || name === 'mobile') return '$.form.phone'
  if (name === 'address') return '$.form.address'

  // IDs
  if (name === 'customerid' || name === 'customer_id') return '$.nodes.reg_customer.output.id'
  if (name === 'facilityid' || name === 'facility_id') return '$.nodes.create_facility.output.id'
  if (name === 'branchid' || name === 'branch_id') return '$.auth.branch_id'
  if (name === 'entityid' || name === 'entity_id') return '$.auth.entity_id'

  // Financial
  if (name === 'amount' || name === 'principalamount' || name === 'principal_amount') return '$.form.amount'
  if (name === 'items') return '$.form.items'

  // Product
  if (name === 'productcode' || name === 'product_code') return 'GOLD_STANDARD'

  // Generic
  if (name.includes('id')) return '$.nodes.' + ctx.nodeKey + '.output.id'
  if (name.includes('amount')) return '$.form.amount'
  if (name.includes('date')) return '$.form.date'

  return '$.form.' + argName
}
