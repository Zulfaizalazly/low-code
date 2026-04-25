/**
 * useConfigFields — Shared config field definitions for flow nodes.
 *
 * Extracted from NodeInspector.vue so that both the inspector and the
 * preflight checker consume the same source of truth.
 */

export interface ConfigFieldDef {
  key: string
  label: string
  type: 'select' | 'text' | 'textarea' | 'json' | 'number' | 'divider'
  options?: string[] | { class: string; name: string; domain: string }[]
  placeholder?: string
}

/**
 * Returns the list of config field definitions for a given node type.
 *
 * @param nodeType  - The node's type string (e.g. 'trigger', 'command', …)
 * @param config    - The node's current config object
 * @param commands  - The available commands list (objects with class/name/domain/arguments)
 */
export function getConfigFields(
  nodeType: string,
  config: Record<string, any>,
  commands: any[]
): ConfigFieldDef[] {
  switch (nodeType) {
    case 'trigger':
      return [
        { key: 'trigger_type', label: 'Trigger Type', type: 'select', options: ['manual_start', 'domain_event', 'api_call', 'scheduled'] },
        { key: 'event_name', label: 'Event Name', type: 'text', placeholder: 'e.g., facility.created' },
      ]
    case 'command': {
      const fields: ConfigFieldDef[] = [
        { key: 'command_class', label: 'Command Class', type: 'select', options: commands },
      ]

      // If a command is selected, show its arguments for mapping
      if (config.command_class) {
        const cmd = commands.find((c: any) => c.class === config.command_class)
        if (cmd && cmd.arguments && cmd.arguments.length > 0) {
          fields.push({ key: '_mapping_title', label: 'Argument Mapping', type: 'divider' })
          cmd.arguments.forEach((arg: any) => {
            fields.push({
              key: `mapping.${arg.name}`,
              label: `${arg.name} (${arg.type})${arg.required ? ' *' : ''}`,
              type: 'text',
              placeholder: 'Context path e.g. $.payload.id',
            })
          })
        }
      }
      return fields
    }
    case 'approval':
      return [
        { key: 'assigned_role', label: 'Assigned Role', type: 'select', options: ['branch_manager', 'hq_admin', 'hq_manager', 'credit_officer'] },
        { key: 'approval_tier', label: 'Minimum Tier', type: 'select', options: ['tier_1', 'tier_2', 'tier_3', 'gold_heavy'] },
        { key: 'sla_hours', label: 'SLA (Hours)', type: 'number' },
      ]
    case 'notification':
      return [
        { key: 'channel', label: 'Channel', type: 'select', options: ['email', 'sms', 'whatsapp', 'internal_inbox'] },
        { key: 'recipient', label: 'Recipient (context path)', type: 'text', placeholder: '$.customer.email' },
        { key: 'template_key', label: 'Template Key', type: 'text' },
        { key: 'subject', label: 'Subject Template', type: 'text' },
      ]
    case 'document':
      return [
        { key: 'template_key', label: 'Template Key', type: 'text', placeholder: 'e.g., surat_gadai_v1' },
        { key: 'output_format', label: 'Output Format', type: 'select', options: ['pdf', 'docx', 'html'] },
        { key: 'attach_to_entity', label: 'Attach to Entity', type: 'text', placeholder: '$.facility.id' },
      ]
    case 'gl_action':
      return [
        { key: 'transaction_code', label: 'Transaction Code', type: 'text' },
        { key: 'amount_path', label: 'Amount Variable', type: 'text', placeholder: '$.total_amount' },
        { key: 'narration', label: 'Narration Template', type: 'text' },
      ]
    case 'decision':
      return [
        { key: 'condition_type', label: 'Condition Type', type: 'select', options: ['simple_match', 'expression', 'outcome_check'] },
        { key: 'expression', label: 'Expression', type: 'textarea', placeholder: 'e.g., payload.amount > 1000' },
      ]
    case 'formula':
      return [
        { key: 'formula_key', label: 'Formula Reference', type: 'text', placeholder: 'e.g., calculate_ujrah' },
        { key: 'input_mapping', label: 'Input Mapping (JSON)', type: 'json' },
        { key: 'result_path', label: 'Store Result In', type: 'text', placeholder: '$.calculated_value' },
      ]
    case 'payment_gateway': {
      const pgFields: ConfigFieldDef[] = [
        { key: 'provider', label: 'Payment Provider', type: 'select', options: ['billplz', 'bayarcash', 'toyyibpay', 'stripe', 'chip'] },
        { key: 'amount', label: 'Amount (Context Path)', type: 'text', placeholder: 'e.g., $.total_redemption' },
        { key: 'description', label: 'Payment Description', type: 'text', placeholder: 'e.g., Ar-Rahnu Repayment' },
        { key: 'type', label: 'Transaction Type', type: 'select', options: ['collection', 'disbursement'] },
        { key: 'output_key', label: 'Store Result In', type: 'text', placeholder: 'payment_response' },
      ]

      const provider = config.provider
      if (provider) {
        pgFields.push({ key: '_provider_creds', label: 'Provider Credentials', type: 'divider' })
      }

      if (provider === 'billplz') {
        pgFields.push({ key: 'credentials.collection_id', label: 'Collection ID', type: 'text' })
        pgFields.push({ key: 'credentials.api_key', label: 'API Secret Key', type: 'text' })
      } else if (provider === 'toyyibpay') {
        pgFields.push({ key: 'credentials.category_code', label: 'Category Code', type: 'text' })
        pgFields.push({ key: 'credentials.user_secret_key', label: 'User Secret Key', type: 'text' })
      } else if (provider === 'stripe') {
        pgFields.push({ key: 'credentials.secret_key', label: 'Secret API Key (sk_live/test)', type: 'text' })
      } else if (provider === 'bayarcash') {
        pgFields.push({ key: 'credentials.portal_key', label: 'Portal Key', type: 'text' })
        pgFields.push({ key: 'credentials.pat', label: 'Personal Access Token (PAT)', type: 'text' })
        pgFields.push({ key: 'credentials.secret_key', label: 'API Secret Key', type: 'text' })
      } else if (provider === 'chip') {
        pgFields.push({ key: 'credentials.brand_id', label: 'Brand ID', type: 'text' })
        pgFields.push({ key: 'credentials.api_key', label: 'API Key', type: 'text' })
      }
      return pgFields
    }
    case 'tawarruq_calc':
      return [
        { key: 'marhun_value', label: 'Marhun Value Path', type: 'text', placeholder: '$.marhun_value' },
        { key: 'margin_rate', label: 'Margin Rate Path', type: 'text', placeholder: '$.margin_rate (e.g. 0.025)' },
        { key: 'ltv_ratio', label: 'LTV Ratio Path', type: 'text', placeholder: '$.ltv_ratio (e.g. 0.70)' },
        { key: 'ujrah_rate', label: 'Ujrah Rate Path', type: 'text', placeholder: '$.ujrah_rate (e.g. 0.75 per 100)' },
        { key: 'tenure_months', label: 'Tenure Months Path', type: 'text', placeholder: '$.tenure_months (e.g. 6)' },
        { key: 'output_key', label: 'Store Result In', type: 'text', placeholder: 'tawarruq' },
      ]
    case 'generate_pdf':
      return [
        { key: 'template_id', label: 'Template ID', type: 'text', placeholder: 'e.g., sag_standard_v1' },
      ]
    case 'vault_action':
      return [
        { key: 'action', label: 'Vault Action', type: 'select', options: ['check_in', 'check_out', 'audit'] },
        { key: 'marhun_id', label: 'Marhun ID Path', type: 'text', placeholder: '$.marhun_id' },
      ]
    case 'api_request': {
      const apiFields: ConfigFieldDef[] = [
        { key: 'method', label: 'HTTP Method', type: 'select', options: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] },
        { key: 'url', label: 'API Endpoint URL', type: 'text', placeholder: 'https://api.example.com/v1/...' },
        { key: 'auth_type', label: 'Authentication', type: 'select', options: ['none', 'bearer', 'basic'] },
      ]

      const authType = config.auth_type
      if (authType === 'bearer') {
        apiFields.push({ key: 'auth_token', label: 'Bearer Token', type: 'text', placeholder: 'e.g., sk_test_...' })
      } else if (authType === 'basic') {
        apiFields.push({ key: 'auth_username', label: 'Username', type: 'text' })
        apiFields.push({ key: 'auth_password', label: 'Password', type: 'text' })
      }

      apiFields.push({ key: 'headers', label: 'Headers (JSON)', type: 'json', placeholder: '{"Content-Type": "application/json"}' })

      if (['POST', 'PUT', 'PATCH'].includes(config.method)) {
        apiFields.push({ key: 'payload', label: 'Request Body (JSON or Mapping)', type: 'json' })
      }

      apiFields.push({ key: 'output_key', label: 'Store Response In', type: 'text', placeholder: 'api_response' })

      return apiFields
    }
    default:
      return []
  }
}
