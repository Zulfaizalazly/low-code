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
          🗑️ Delete Node
        </button>
      </div>
    </template>

    <template v-else>
      <div class="empty-state">
        <span class="empty-icon">👆</span>
        <p>Select a node to edit its properties</p>
      </div>
    </template>
  </aside>
</template>

<style scoped>
.node-inspector {
  width: 280px;
  background: rgba(15, 23, 42, 0.6);
  border-left: 1px solid rgba(255,255,255,0.05);
  padding: 16px;
  overflow-y: auto;
  flex-shrink: 0;
  backdrop-filter: blur(12px);
  transition: opacity 0.2s;
}

.inspector-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}

.inspector-header h3 {
  font-size: 14px;
  font-weight: 600;
  color: #e2e8f0;
  margin: 0;
}

.node-type-badge {
  font-size: 10px;
  padding: 3px 8px;
  background: rgba(99, 102, 241, 0.15);
  color: #a5b4fc;
  border-radius: 6px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.field-group {
  margin-bottom: 14px;
}

.field-label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  margin-bottom: 5px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.field-input {
  width: 100%;
  padding: 8px 10px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  color: #e2e8f0;
  font-size: 12px;
  outline: none;
  transition: border-color 0.15s;
  box-sizing: border-box;
}

.field-input:focus {
  border-color: rgba(99, 102, 241, 0.4);
  background: rgba(255,255,255,0.06);
}

.field-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.field-textarea {
  resize: vertical;
  font-family: 'JetBrains Mono', 'SF Mono', monospace;
}

.field-json {
  font-size: 11px;
  line-height: 1.4;
}

.config-divider {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: #64748b;
  margin: 20px 0 12px;
  padding-top: 12px;
  border-top: 1px solid rgba(255,255,255,0.06);
}

.inspector-actions {
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid rgba(255,255,255,0.06);
}

.btn-delete {
  width: 100%;
  padding: 8px 12px;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 8px;
  color: #f87171;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.4);
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 200px;
  text-align: center;
  color: #475569;
}

.empty-icon {
  font-size: 32px;
  margin-bottom: 12px;
  opacity: 0.5;
}

.empty-state p {
  font-size: 13px;
  margin: 0;
}
</style>
