<script setup>
/**
 * NodeInspector — Properties panel for selected node.
 * Shows node config fields and allows editing.
 */
import { ref, watch, computed } from 'vue'

const props = defineProps({
  node: { type: Object, default: null },
  commands: { type: Array, default: () => [] },
})

const emit = defineEmits(['update', 'delete'])

const localConfig = ref({})
const localLabel = ref('')

watch(() => props.node, (newNode) => {
  if (newNode) {
    localLabel.value = newNode.label || ''
    localConfig.value = { ...(newNode.config || {}) }
  }
}, { immediate: true, deep: true })

function save() {
  emit('update', {
    id: props.node.id,
    label: localLabel.value,
    config: { ...localConfig.value },
  })
}

function confirmDelete() {
  if (confirm(`Delete node "${localLabel.value}"?`)) {
    emit('delete', props.node.id)
  }
}

// ─── Value Helpers (handles nested keys like "mapping.field") ───
function getConfigValue(key) {
  if (key.includes('.')) {
    const [branch, leaf] = key.split('.')
    return localConfig.value[branch] ? localConfig.value[branch][leaf] : ''
  }
  return localConfig.value[key]
}

function setConfigValue(key, value) {
  if (key.includes('.')) {
    const [branch, leaf] = key.split('.')
    if (!localConfig.value[branch]) localConfig.value[branch] = {}
    localConfig.value[branch][leaf] = value
  } else {
    localConfig.value[key] = value
  }
  save()
}

// ─── Dynamic config fields based on node type ───
const configFields = computed(() => {
  if (!props.node) return []

  const type = props.node.nodeType
  switch (type) {
    case 'trigger':
      return [
        { key: 'trigger_type', label: 'Trigger Type', type: 'select', options: ['manual_start', 'domain_event', 'api_call', 'scheduled'] },
        { key: 'event_name', label: 'Event Name', type: 'text', placeholder: 'e.g., facility.created' },
      ]
    case 'command':
      const fields = [
        { key: 'command_class', label: 'Command Class', type: 'select', options: props.commands },
      ]
      
      // If a command is selected, show its arguments for mapping
      if (localConfig.value.command_class) {
        const cmd = props.commands.find(c => c.class === localConfig.value.command_class)
        if (cmd && cmd.arguments && cmd.arguments.length > 0) {
          fields.push({ key: '_mapping_title', label: 'Argument Mapping', type: 'divider' })
          cmd.arguments.forEach(arg => {
            fields.push({ 
              key: `mapping.${arg.name}`, 
              label: `${arg.name} (${arg.type})${arg.required ? ' *' : ''}`, 
              type: 'text', 
              placeholder: 'Context path e.g. $.payload.id' 
            })
          })
        }
      }
      return fields
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
    case 'payment_gateway':
      const pgFields = [
        { key: 'provider', label: 'Payment Provider', type: 'select', options: ['billplz', 'bayarcash', 'toyyibpay', 'stripe', 'chip'] },
        { key: 'amount', label: 'Amount (Context Path)', type: 'text', placeholder: 'e.g., $.total_redemption' },
        { key: 'description', label: 'Payment Description', type: 'text', placeholder: 'e.g., Ar-Rahnu Repayment' },
        { key: 'type', label: 'Transaction Type', type: 'select', options: ['collection', 'disbursement'] },
        { key: 'output_key', label: 'Store Result In', type: 'text', placeholder: 'payment_response' },
      ];
      
      const provider = localConfig.value.provider;
      if (provider) {
        pgFields.push({ key: '_provider_creds', label: 'Provider Credentials', type: 'divider' });
      }

      if (provider === 'billplz') {
         pgFields.push({ key: 'credentials.collection_id', label: 'Collection ID', type: 'text' });
         pgFields.push({ key: 'credentials.api_key', label: 'API Secret Key', type: 'text' });
      } else if (provider === 'toyyibpay') {
         pgFields.push({ key: 'credentials.category_code', label: 'Category Code', type: 'text' });
         pgFields.push({ key: 'credentials.user_secret_key', label: 'User Secret Key', type: 'text' });
      } else if (provider === 'stripe') {
         pgFields.push({ key: 'credentials.secret_key', label: 'Secret API Key (sk_live/test)', type: 'text' });
      } else if (provider === 'bayarcash') {
         pgFields.push({ key: 'credentials.portal_key', label: 'Portal Key', type: 'text' });
         pgFields.push({ key: 'credentials.pat', label: 'Personal Access Token (PAT)', type: 'text' });
         pgFields.push({ key: 'credentials.secret_key', label: 'API Secret Key', type: 'text' });
      } else if (provider === 'chip') {
         pgFields.push({ key: 'credentials.brand_id', label: 'Brand ID', type: 'text' });
         pgFields.push({ key: 'credentials.api_key', label: 'API Key', type: 'text' });
      }
      return pgFields;
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
    case 'api_request':
      const apiFields = [
        { key: 'method', label: 'HTTP Method', type: 'select', options: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] },
        { key: 'url', label: 'API Endpoint URL', type: 'text', placeholder: 'https://api.example.com/v1/...' },
        { key: 'auth_type', label: 'Authentication', type: 'select', options: ['none', 'bearer', 'basic'] },
      ];
      
      const authType = localConfig.value.auth_type;
      if (authType === 'bearer') {
        apiFields.push({ key: 'auth_token', label: 'Bearer Token', type: 'text', placeholder: 'e.g., sk_test_...' });
      } else if (authType === 'basic') {
        apiFields.push({ key: 'auth_username', label: 'Username', type: 'text' });
        apiFields.push({ key: 'auth_password', label: 'Password', type: 'text' });
      }

      apiFields.push({ key: 'headers', label: 'Headers (JSON)', type: 'json', placeholder: '{"Content-Type": "application/json"}' });
      
      if (['POST', 'PUT', 'PATCH'].includes(localConfig.value.method)) {
        apiFields.push({ key: 'payload', label: 'Request Body (JSON or Mapping)', type: 'json' });
      }
      
      apiFields.push({ key: 'output_key', label: 'Store Response In', type: 'text', placeholder: 'api_response' });
      
      return apiFields;
    default:
      return []
  }
})
</script>

<template>
  <aside class="node-inspector" :class="{ active: !!node }">
    <template v-if="node">
      <div class="inspector-header">
        <h3>Node Properties</h3>
        <span class="node-type-badge">{{ node.nodeType }}</span>
      </div>

      <!-- Basic Info -->
      <div class="field-group">
        <label class="field-label">Label</label>
        <input
          v-model="localLabel"
          type="text"
          class="field-input"
          @change="save"
        />
      </div>

      <div class="field-group">
        <label class="field-label">Key</label>
        <input
          :value="node.nodeKey"
          type="text"
          class="field-input"
          disabled
        />
      </div>

      <!-- Dynamic Config Fields -->
      <div class="config-divider">Configuration</div>

      <div v-for="field in configFields" :key="field.key" class="field-group">
        <template v-if="field.type === 'divider'">
          <div class="config-divider">{{ field.label }}</div>
        </template>
        <template v-else>
          <label class="field-label">{{ field.label }}</label>

          <select
            v-if="field.type === 'select' && Array.isArray(field.options) && typeof field.options[0] === 'string'"
            :value="getConfigValue(field.key)"
            class="field-input"
            @change="(e) => setConfigValue(field.key, e.target.value)"
          >
            <option value="">Select...</option>
            <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
          </select>

          <select
            v-else-if="field.type === 'select' && field.key === 'command_class'"
            :value="getConfigValue(field.key)"
            class="field-input"
            @change="(e) => setConfigValue(field.key, e.target.value)"
          >
            <option value="">Select command...</option>
            <option v-for="cmd in commands" :key="cmd.class" :value="cmd.class">
              {{ cmd.name }} ({{ cmd.domain }})
            </option>
          </select>

          <textarea
            v-else-if="field.type === 'textarea'"
            :value="getConfigValue(field.key)"
            class="field-input field-textarea"
            rows="3"
            :placeholder="field.placeholder"
            @change="(e) => setConfigValue(field.key, e.target.value)"
          />

          <textarea
            v-else-if="field.type === 'json'"
            :value="JSON.stringify(getConfigValue(field.key) || {}, null, 2)"
            class="field-input field-textarea field-json"
            rows="4"
            @change="(e) => { try { setConfigValue(field.key, JSON.parse(e.target.value)) } catch {} }"
          />

          <input
            v-else-if="field.type === 'number'"
            :value="getConfigValue(field.key)"
            type="number"
            class="field-input"
            @change="(e) => setConfigValue(field.key, parseFloat(e.target.value))"
          />

          <input
            v-else
            :value="getConfigValue(field.key)"
            type="text"
            class="field-input"
            :placeholder="field.placeholder"
            @change="(e) => setConfigValue(field.key, e.target.value)"
          />
        </template>
      </div>

      <!-- Actions -->
      <div class="inspector-actions">
        <button class="btn-delete" @click="confirmDelete">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"></path></svg>
          Remove Node
        </button>
      </div>
    </template>

    <template v-else>
      <div class="empty-state">
        <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
        <p>Select a node to view its configurations</p>
      </div>
    </template>
  </aside>
</template>

<style scoped>
.node-inspector {
  position: absolute;
  top: 0;
  right: 0;
  height: 100%;
  width: 340px;
  background: #ffffff;
  border-left: 1px solid rgba(0, 0, 0, 0.08);
  padding: 24px;
  overflow-y: auto;
  flex-shrink: 0;
  z-index: 100;
  
  /* Slide-in animation */
  transform: translateX(100%);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: -4px 0 24px rgba(0, 0, 0, 0.04);
}

.node-inspector.active {
  transform: translateX(0);
}

.inspector-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.inspector-header h3 {
  font-size: 16px;
  font-weight: 500;
  color: #202124;
  margin: 0;
  letter-spacing: -0.01em;
  font-family: "Google Sans", "Inter", sans-serif;
}

.node-type-badge {
  font-size: 11px;
  padding: 4px 10px;
  background: #e8f0fe;
  color: #1a73e8;
  border-radius: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.field-group {
  margin-bottom: 20px;
}

.field-label {
  display: block;
  font-size: 12px;
  font-weight: 500;
  color: #5f6368;
  margin-bottom: 8px;
}

.field-input {
  width: 100%;
  padding: 10px 12px;
  background: #ffffff;
  border: 1px solid #dadce0;
  border-radius: 6px;
  color: #202124;
  font-size: 13px;
  outline: none;
  transition: all 0.2s;
  box-sizing: border-box;
  font-family: inherit;
}

.field-input:hover:not(:disabled) {
  border-color: #9aa0a6;
}

.field-input:focus {
  border-color: #1a73e8;
  box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
}

.field-input:disabled {
  background: #f1f3f4;
  color: #80868b;
  cursor: not-allowed;
  border-color: #e8eaed;
}

.field-textarea {
  resize: vertical;
  min-height: 80px;
}

.field-json {
  font-family: 'JetBrains Mono', 'SF Mono', Consolas, monospace;
  font-size: 12px;
  line-height: 1.5;
  background: #f8f9fa;
}

.config-divider {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #1a73e8;
  margin: 28px 0 16px;
  padding-top: 16px;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.inspector-actions {
  margin-top: 32px;
  padding-top: 20px;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.btn-delete {
  width: 100%;
  padding: 10px 16px;
  background: #fff;
  border: 1px solid #d93025;
  border-radius: 6px;
  color: #d93025;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-delete:hover {
  background: #fce8e6;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  text-align: center;
  color: #80868b;
}

.empty-icon {
  width: 48px;
  height: 48px;
  margin-bottom: 16px;
  color: #dadce0;
}

.empty-state p {
  font-size: 14px;
  margin: 0;
  font-weight: 500;
}
</style>
