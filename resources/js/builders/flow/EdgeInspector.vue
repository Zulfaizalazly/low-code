<script setup>
/**
 * EdgeInspector — Property editor for flow edges (connections).
 * Primarily used for setting conditional logic on decision branches.
 */
import { ref, watch } from 'vue'

const props = defineProps({
  edge: Object,
})

const emit = defineEmits(['update', 'delete'])

const localConditionType = ref('always')
const localConditionConfig = ref({})

watch(() => props.edge, (newEdge) => {
  if (newEdge) {
    localConditionType.value = newEdge.conditionType || 'always'
    localConditionConfig.value = { ...newEdge.conditionConfig }
  }
}, { immediate: true })

function save() {
  emit('update', {
    id: props.edge.id,
    conditionType: localConditionType.value,
    conditionConfig: localConditionConfig.value,
  })
}

function confirmDelete() {
  if (confirm('Remove this connection?')) {
    emit('delete', props.edge.id)
  }
}
</script>

<template>
  <aside class="edge-inspector" :class="{ active: !!edge }">
    <template v-if="edge">
      <header class="inspector-header">
        <div class="insp-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="color: #5f6368;"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
          <h3>Edge Properties</h3>
        </div>
      </header>

      <div class="insp-body">
        <div class="field-group">
          <label class="field-label">Condition Type</label>
          <select v-model="localConditionType" class="field-input" @change="save">
            <option value="always">Always (Default)</option>
            <option value="outcome">Based on Node Outcome</option>
            <option value="expression">Custom Expression</option>
          </select>
        </div>

        <div v-if="localConditionType === 'outcome'" class="field-group">
          <label class="field-label">Expected Outcome</label>
          <input 
            v-model="localConditionConfig.outcome" 
            type="text" 
            placeholder="e.g. success, approved" 
            class="field-input" 
            @change="save"
          />
        </div>

        <div v-if="localConditionType === 'expression'" class="field-group">
          <label class="field-label">Boolean Expression</label>
          <textarea 
            v-model="localConditionConfig.expression" 
            placeholder="e.g. context.amount > 1000" 
            class="field-input field-textarea" 
            rows="3" 
            @change="save"
          ></textarea>
        </div>

        <div class="inspector-actions">
          <button class="btn-delete" @click="confirmDelete">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"></path></svg>
            Remove Edge
          </button>
        </div>
      </div>
    </template>
  </aside>
</template>

<style scoped>
.edge-inspector {
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

.edge-inspector.active {
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

.insp-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.inspector-header h3 {
  font-size: 16px;
  font-weight: 500;
  color: #202124;
  margin: 0;
  letter-spacing: -0.01em;
  font-family: "Google Sans", "Inter", sans-serif;
}

.insp-body {
  display: flex;
  flex-direction: column;
  gap: 16px;
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
  text-transform: uppercase;
  letter-spacing: 0.5px;
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
  font-family: 'JetBrains Mono', 'SF Mono', Consolas, monospace;
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
</style>
