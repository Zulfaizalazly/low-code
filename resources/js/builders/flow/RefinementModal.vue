<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  show: Boolean,
  definition: Object,
  aspects: Array,
  options: Object,
  iterationCount: Number,
})

const emit = defineEmits(['close', 'applied'])

const selectedAspects = ref([])
const activeTab = ref('Fields')
const instructionText = ref('')
const searchText = ref('')
const isRefining = ref(false)
const showPreview = ref(false)
const previewChanges = ref([])

const tabs = ['Fields', 'Steps', 'Validation', 'Layout']

const filteredAspects = computed(() => {
  const base = activeTab.value === 'Fields' 
    ? props.aspects.filter(a => a.type === 'field_property')
    : props.aspects.filter(a => a.type === 'step_property')

  if (!searchText.value) return base
  return base.filter(a => a.label.toLowerCase().includes(searchText.value.toLowerCase()))
})


const isLimitReached = computed(() => (props.iterationCount || 0) >= 5)

function toggleAspect(aspect) {
  const index = selectedAspects.value.findIndex(a => a.target === aspect.target)
  if (index === -1) {
    selectedAspects.value.push({ ...aspect, selectedProperties: [] })
  } else {
    selectedAspects.value.splice(index, 1)
  }
  updatePreview()
}

function isSelected(aspect) {
  return selectedAspects.value.some(a => a.target === aspect.target)
}

function updatePreview() {
  // Generate preview of changes
  previewChanges.value = selectedAspects.value.map(a => ({
    target: a.label,
    changes: a.selectedProperties.join(', '),
  }))
  showPreview.value = selectedAspects.value.length > 0
}

function applyRefinement() {
  if (!instructionText.value && selectedAspects.value.length === 0) return

  isRefining.value = true
  
  // Construct structured instruction if selections exist
  let finalInstruction = instructionText.value
  if (selectedAspects.value.length > 0) {
    const selections = selectedAspects.value.map(a => `${a.label}: ${a.selectedProperties.join(', ')}`).join('; ')
    finalInstruction = `Refine the following aspects: ${selections}. ` + finalInstruction
  }

  // Call Livewire refineUI
  if (window.Livewire) {
    window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
      .refineUI(props.definition, finalInstruction)
  }
}

// Global listeners for refinement result
window.addEventListener('ui-refined', (event) => {
  isRefining.value = false
  emit('applied', event.detail[0])
})

window.addEventListener('ui-refinement-failed', (event) => {
  isRefining.value = false
  const detail = event.detail[0] || event.detail
  const reportUrl = `/studio/support/report-issue?context=${encodeURIComponent(JSON.stringify(detail.error_context || {}))}`
  
  if (confirm(`Refinement failed: ${detail.message}\n\nWould you like to report this issue?`)) {
    window.open(reportUrl, '_blank')
  }
})
</script>

<template>
  <Transition name="fade">
    <div v-if="show" class="refinement-overlay" @click.self="$emit('close')">
      <Transition name="slide-up">
        <div v-if="show" class="refinement-modal liquid-glass">
          <div class="refinement-header">
            <div class="header-main">
              <h3>✨ Visual Refinement Engine</h3>
              <span class="iteration-badge" :class="{ warning: iterationCount >= 4 }">
                Iteration {{ iterationCount || 0 }}/5
              </span>
            </div>
            <button @click="$emit('close')" class="close-btn">&times;</button>
          </div>

      <div class="refinement-body">
        <div class="refinement-search">
          <input v-model="searchText" type="text" placeholder="Search fields or steps..." class="search-input">
        </div>

        <div class="refinement-tabs">
          <button 
            v-for="tab in tabs" 
            :key="tab"
            @click="activeTab = tab"
            :class="['tab-btn', { active: activeTab === tab }]"
          >
            {{ tab }}
          </button>
        </div>

        <div class="aspects-list">
          <div v-if="filteredAspects.length === 0" class="empty-state">
            No aspects detected for this category.
          </div>
          
          <div 
            v-for="aspect in filteredAspects" 
            :key="aspect.target"
            :class="['aspect-item', { selected: isSelected(aspect) }]"
            @click="toggleAspect(aspect)"
          >
            <div class="aspect-info">
              <span class="aspect-label">{{ aspect.label }}</span>
              <span class="aspect-target">{{ aspect.target }}</span>
            </div>
            
            <div v-if="isSelected(aspect)" class="aspect-options" @click.stop>
              <div v-for="prop in aspect.properties" :key="prop" class="prop-checkbox">
                <label>
                  <input type="checkbox" v-model="selectedAspects.find(a => a.target === aspect.target).selectedProperties" :value="prop">
                  {{ prop }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="manual-instruction">
          <label>Additional Instructions (Natural Language)</label>
          <textarea 
            v-model="instructionText" 
            placeholder="e.g., 'Make the IC field mandatory and move it to the top of Step 1'"
            :disabled="isLimitReached"
          ></textarea>
          <div v-if="isLimitReached" class="limit-warning">
            Maximum iteration limit (5) reached for this session.
          </div>
        </div>

        <!-- Live Preview Pane -->
        <div v-if="showPreview" class="preview-pane">
          <h4>Preview Changes</h4>
          <div class="preview-list">
            <div v-for="(change, idx) in previewChanges" :key="idx" class="preview-item">
              <span class="preview-target">{{ change.target }}</span>
              <span class="preview-arrow">→</span>
              <span class="preview-changes">{{ change.changes }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="refinement-footer">

        <div class="footer-actions">
          <button @click="$emit('close')" class="btn-secondary">Cancel</button>
          <button 
            @click="applyRefinement" 
            class="btn-primary-gradient"
            :disabled="isRefining || isLimitReached || (!instructionText && selectedAspects.length === 0)"
          >
            <span v-if="isRefining">✨ Refining...</span>
            <span v-else>Apply Refinement</span>
          </button>
        </div>
      </div>
    </div>
      </Transition>
    </div>
  </Transition>
</template>

<style scoped>
.refinement-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.refinement-modal {
  width: 100%;
  max-width: 600px;
  background: rgba(15, 23, 42, 0.95);
  border-radius: 20px;
  display: flex;
  flex-direction: column;
}

.refinement-header {
  padding: 16px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-main {
  display: flex;
  align-items: center;
  gap: 12px;
}

.iteration-badge {
  font-size: 10px;
  background: rgba(255, 255, 255, 0.1);
  padding: 2px 8px;
  border-radius: 40px;
  color: #94a3b8;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.iteration-badge.warning {
  border-color: #f59e0b;
  color: #f59e0b;
  background: rgba(245, 158, 11, 0.1);
}

.refinement-header h3 {
  margin: 0;
  font-size: 16px;
  color: #a5b4fc;
}

.refinement-body {
  padding: 20px;
  flex: 1;
  overflow-y: auto;
}

.refinement-search {
  margin-bottom: 16px;
}

.search-input {
  width: 100%;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 8px 12px;
  color: white;
  font-size: 13px;
  outline: none;
}

.refinement-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
}

.tab-btn {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #94a3b8;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 13px;
  cursor: pointer;
}

.tab-btn.active {
  background: rgba(99, 102, 241, 0.2);
  border-color: #6366f1;
  color: white;
}

.aspects-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 20px;
}

.aspect-item {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
  padding: 12px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.aspect-item.selected {
  border-color: #6366f1;
  background: rgba(99, 102, 241, 0.05);
}

.aspect-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.aspect-label {
  font-weight: 600;
  color: #e2e8f0;
}

.aspect-target {
  font-size: 11px;
  color: #64748b;
}

.aspect-options {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  padding-top: 8px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.prop-checkbox {
  font-size: 12px;
  color: #94a3b8;
}

.manual-instruction {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.manual-instruction label {
  font-size: 13px;
  color: #a5b4fc;
}

.manual-instruction textarea {
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 12px;
  color: white;
  font-size: 13px;
  resize: none;
  height: 80px;
}

.manual-instruction textarea:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.limit-warning {
  color: #f87171;
  font-size: 11px;
  margin-top: 4px;
}

.preview-pane {
  margin-top: 20px;
  padding: 16px;
  background: rgba(99, 102, 241, 0.05);
  border: 1px solid rgba(99, 102, 241, 0.2);
  border-radius: 12px;
}

.preview-pane h4 {
  margin: 0 0 12px 0;
  font-size: 13px;
  color: #a5b4fc;
  font-weight: 600;
}

.preview-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.preview-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 8px;
  font-size: 12px;
}

.preview-target {
  color: #e2e8f0;
  font-weight: 600;
}

.preview-arrow {
  color: #6366f1;
}

.preview-changes {
  color: #94a3b8;
  font-style: italic;
}

.refinement-footer {
  padding: 16px 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.footer-actions {
  display: flex;
  gap: 12px;
}

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-up-enter-active, .slide-up-leave-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translateY(20px) scale(0.98); }
</style>
