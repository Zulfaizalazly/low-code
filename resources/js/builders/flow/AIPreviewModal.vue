<script setup>
import { ref, computed } from 'vue'
import RefinementModal from './RefinementModal.vue'

const props = defineProps({
  show: Boolean,
  definition: Object,
  aspects: Array,
  options: Object,
  iterationCount: Number,
})

const emit = defineEmits(['close', 'publish', 'manual-override'])

const showRefinement = ref(false)

const complianceScore = computed(() => props.definition?._validation?.score || 100)
const scoreColor = computed(() => {
  if (complianceScore.value >= 90) return '#10b981' // Success
  if (complianceScore.value >= 70) return '#f59e0b' // Warning
  return '#ef4444' // Danger
})

function onRefine() {
  showRefinement.value = true
}

function closeRefinement() {
  showRefinement.value = false
}

function handleRefinementApplied(newResult) {
  // Update the definition with refined one
  // This is handled by the parent listening to 'ui-refined' event
  showRefinement.value = false
}
</script>

<template>
  <div v-if="show" class="ai-modal-overlay">
    <div class="ai-modal-content liquid-glass">
      <div class="ai-modal-header">
        <h2>✨ AI-Generated Preview</h2>
        <button @click="$emit('close')" class="close-btn">&times;</button>
      </div>

      <div class="ai-modal-body">
        <div class="preview-container">
          <div class="preview-explanation">
            <div class="compliance-header">
              <p>AI has generated a multi-step form based on your workflow.</p>
              <div class="compliance-badge" :style="{ borderColor: scoreColor, color: scoreColor }">
                Design Compliance: {{ complianceScore }}%
              </div>
            </div>
          </div>
          
          <div class="definition-viewer">
            <pre>{{ JSON.stringify(definition, null, 2) }}</pre>
          </div>

          <div v-if="complianceScore < 90" class="design-warnings">
            <p v-for="v in definition._validation.violations" :key="v.target">
              ⚠️ <strong>{{ v.target }}</strong>: {{ v.message }}
            </p>
          </div>
        </div>
      </div>

      <div class="ai-modal-footer">
        <button @click="$emit('manual-override')" class="btn-secondary">Manual Override</button>
        <button @click="onRefine" class="btn-primary-gradient">Refine with AI</button>
        <button @click="$emit('publish')" class="btn-success">Accept & Publish</button>
      </div>
    </div>

    <!-- Refinement Modal Nesting -->
    <RefinementModal
      :show="showRefinement"
      :definition="definition"
      :aspects="aspects"
      :options="options"
      :iteration-count="iterationCount"
      @close="closeRefinement"
      @applied="handleRefinementApplied"
    />
  </div>
</template>

<style scoped>
.ai-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.liquid-glass {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
}

.ai-modal-content {
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  color: white;
}

.ai-modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.ai-modal-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  background: linear-gradient(135deg, #fff 0%, #a5b4fc 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.close-btn {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 24px;
  cursor: pointer;
}

.preview-explanation {
  margin-bottom: 16px;
}

.compliance-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.compliance-badge {
  padding: 4px 10px;
  border-radius: 8px;
  border: 1px solid;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.design-warnings {
  margin-top: 16px;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  padding: 12px;
  border-radius: 12px;
  font-size: 12px;
  color: #fca5a5;
}

.design-warnings p {
  margin: 4px 0;
}

.definition-viewer {
  background: rgba(0, 0, 0, 0.3);
  padding: 16px;
  border-radius: 12px;
  font-family: 'SF Mono', 'Fira Code', monospace;
  font-size: 13px;
  overflow-x: auto;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.ai-modal-footer {
  padding: 20px 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-primary-gradient {
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: #e2e8f0;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 10px 20px;
  border-radius: 10px;
  cursor: pointer;
}

.btn-success {
  background: #10b981;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
}
</style>
