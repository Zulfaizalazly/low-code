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
</script>

<template>
  <aside v-if="edge" class="edge-inspector">
    <header class="insp-header">
      <div class="insp-title">
        <span>🔗</span>
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
    </div>
  </aside>
</template>

<style scoped>
.edge-inspector {
  width: 300px; background: #0f172a; border-left: 1px solid rgba(255,255,255,0.05);
  display: flex; flex-direction: column; z-index: 20;
}
.insp-header { padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.05); }
.insp-title { display: flex; align-items: center; gap: 10px; }
.insp-title h3 { margin: 0; font-size: 14px; color: #f1f5f9; }
.insp-body { padding: 16px; display: flex; flex-direction: column; gap: 16px; }
.field-group { display: flex; flex-direction: column; gap: 6px; }
.field-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; }
.field-input {
  background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px; padding: 10px; color: #cbd5e1; font-size: 13px;
}
.field-textarea { resize: vertical; min-height: 80px; font-family: monospace; }
</style>
