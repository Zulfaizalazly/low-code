<script setup>
/**
 * NodeInspector — Properties panel for selected node.
 * Shows node config fields and allows editing.
 */
import { ref, watch, computed } from 'vue'
import { getConfigFields } from './composables/useConfigFields'

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
  return getConfigFields(props.node.nodeType, localConfig.value, props.commands)
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
