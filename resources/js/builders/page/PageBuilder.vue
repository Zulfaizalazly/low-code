<script setup>
/**
 * PageBuilder — Visual form designer with drag-drop fields.
 * Manages form steps and fields for the Page Definition.
 */
import { ref, computed, onMounted } from 'vue'
import PagePreviewModal from './PagePreviewModal.vue'

const props = defineProps({
  pageId: String,
  versionId: [String, Number],
  initialSteps: { type: Array, default: () => [] },
  initialEntities: { type: Object, default: () => ({}) },
})

const steps = ref([])
const entities = ref(props.initialEntities)
const activeStepIdx = ref(0)
const selectedField = ref(null)
const isDirty = ref(false)
const showPreview = ref(false)
const previewMode = ref('desktop')

const featureName = ref(document.getElementById('page-builder')?.getAttribute('data-feature-name') || '')
const flowName = ref(document.getElementById('page-builder')?.getAttribute('data-flow-name') || '')

const canvasWidth = computed(() => {
  if (previewMode.value === 'mobile') return '375px'
  if (previewMode.value === 'tablet') return '768px'
  return '100%'
})

// ─── Auto-save logic ───
onMounted(() => {
  setInterval(() => {
    if (isDirty.value) {
      console.log('Auto-saving page...')
      savePage()
    }
  }, 30000)
})

// ─── Field Type Library ───
const fieldCategories = [
  {
    name: 'Standard Inputs',
    types: [
      { type: 'text_input', label: 'Text Input', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>', dataType: 'string' },
      { type: 'amount_input', label: 'Currency', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>', dataType: 'decimal' },
      { type: 'date_picker', label: 'Date Picker', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>', dataType: 'date' },
      { type: 'select', label: 'Dropdown', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><path d="M8 12l4 4 4-4"></path></svg>', dataType: 'string' },
      { type: 'textarea', label: 'Text Area', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>', dataType: 'text' },
      { type: 'checkbox', label: 'Checkbox', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>', dataType: 'boolean' },
      { type: 'radio', label: 'Radio Group', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle></svg>', dataType: 'string' },
      { type: 'file_upload', label: 'File Upload', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="12" y2="12"></line><line x1="15" y1="15" x2="12" y2="12"></line></svg>', dataType: 'file' },
    ]
  },
  {
    name: 'Domain Specific',
    types: [
      { type: 'ic_input', label: 'IC Number', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect><circle cx="9" cy="10" r="2"></circle><path d="M15 8h2"></path><path d="M15 12h2"></path><path d="M7 16h5"></path></svg>', dataType: 'string' },
      { type: 'phone_input', label: 'Phone Number', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>', dataType: 'string' },
      { type: 'gold_repeater', label: 'Gold Items', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>', dataType: 'collection' },
      { type: 'nominee_repeater', label: 'Nominees', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>', dataType: 'collection' },
    ]
  },
  {
    name: 'Display & Layout',
    types: [
      { type: 'summary_panel', label: 'Summary Panel', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3zM3 9h18M9 21V9"></path></svg>', dataType: 'display' },
      { type: 'timeline', label: 'Timeline', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>', dataType: 'display' },
      { type: 'alert', label: 'Alert Banner', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>', dataType: 'display' },
      { type: 'badge', label: 'Status Badge', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>', dataType: 'display' },
    ]
  }
]

const activeStep = computed(() => steps.value[activeStepIdx.value] || null)

// ─── Initialize ───
onMounted(() => {
  if (props.initialSteps.length > 0) {
    steps.value = props.initialSteps.map(step => ({
      ...step,
      fields: (step.fields || []).map(f => ({ ...f })),
    }))
  } else {
    steps.value = [{
      step_key: 'step_1',
      title: 'Step 1',
      description: '',
      entity_binding: '',
      sort_order: 0,
      fields: [],
    }]
  }
})

// ─── Step Management ───
function addStep() {
  const idx = steps.value.length + 1
  steps.value.push({
    step_key: `step_${idx}`,
    title: `Step ${idx}`,
    description: '',
    entity_binding: '',
    sort_order: idx - 1,
    fields: [],
  })
  activeStepIdx.value = steps.value.length - 1
  isDirty.value = true
}

function removeStep(idx) {
  if (steps.value.length <= 1) return
  if (!confirm(`Remove "${steps.value[idx].title}"?`)) return
  steps.value.splice(idx, 1)
  if (activeStepIdx.value >= steps.value.length) {
    activeStepIdx.value = steps.value.length - 1
  }
  isDirty.value = true
}

// ─── Field Drag & Drop ───
let fieldCounter = 100

function onFieldDragStart(event, fieldType) {
  event.dataTransfer.setData('application/fieldType', JSON.stringify(fieldType))
  event.dataTransfer.effectAllowed = 'copy'
}

function onFieldDrop(event) {
  event.preventDefault()
  const data = event.dataTransfer.getData('application/fieldType')
  if (!data) return

  const fieldType = JSON.parse(data)
  const key = `${fieldType.type}_${fieldCounter++}`

  activeStep.value.fields.push({
    field_key: key,
    label: fieldType.label,
    component_type: fieldType.type,
    data_type: fieldType.dataType,
    is_required: false,
    default_value: null,
    placeholder: '',
    help_text: '',
    sort_order: activeStep.value.fields.length,
    config: {},
    binding: {
      binding_type: 'direct',
      target_entity: '',
      target_path: '',
    },
  })
  isDirty.value = true
}

function onFieldDragOver(event) {
  event.preventDefault()
  event.dataTransfer.dropEffect = 'copy'
}

// ─── Field Reordering ───
let dragFieldIdx = null

function onReorderStart(event, idx) {
  dragFieldIdx = idx
  event.dataTransfer.effectAllowed = 'move'
}

function onReorderDrop(event, dropIdx) {
  event.preventDefault()
  if (dragFieldIdx === null || dragFieldIdx === dropIdx) return
  const fields = activeStep.value.fields
  const [moved] = fields.splice(dragFieldIdx, 1)
  fields.splice(dropIdx, 0, moved)
  fields.forEach((f, i) => f.sort_order = i)
  dragFieldIdx = null
  isDirty.value = true
}

// ─── Field Selection ───
function selectField(field) {
  selectedField.value = { ...field }
}

function updateField() {
  if (!selectedField.value) return
  const idx = activeStep.value.fields.findIndex(f => f.field_key === selectedField.value.field_key)
  if (idx !== -1) {
    activeStep.value.fields[idx] = { ...selectedField.value }
    isDirty.value = true
  }
}

function removeField(fieldKey) {
  activeStep.value.fields = activeStep.value.fields.filter(f => f.field_key !== fieldKey)
  if (selectedField.value?.field_key === fieldKey) selectedField.value = null
  isDirty.value = true
}

// ─── Save ───
async function savePage() {
  const serialized = steps.value.map((step, si) => ({
    step_key: step.step_key,
    title: step.title,
    description: step.description,
    entity_binding: step.entity_binding,
    sort_order: si,
    fields: step.fields.map((field, fi) => ({
      ...field,
      sort_order: fi,
    })),
  }))

  return new Promise((resolve) => {
    window.dispatchEvent(new CustomEvent('vue-page-save', {
      detail: { steps: serialized }
    }))
    isDirty.value = false
    setTimeout(resolve, 300)
  })
}

window.savePageToLivewire = savePage

// ─── AI UI Generation ───
const isGenerating = ref(false)
async function triggerAIGeneration() {
  if (isDirty.value) {
    if (!confirm('You have unsaved changes. Save now and generate UI?')) return
    await savePage()
  }
  isGenerating.value = true
  if (window.Livewire) {
    window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).generateUI()
  }
}

window.addEventListener('ui-generated', (event) => {
  isGenerating.value = false
  const def = event.detail[0]?.definition || event.detail[0] || {}
  if (def.steps) {
    steps.value = def.steps.map((step, idx) => ({
      ...step,
      sort_order: idx,
      fields: (step.fields || []).map((f, fIdx) => ({
        ...f,
        sort_order: fIdx,
        field_key: f.field_key || `${f.component_type || 'text'}_${Math.floor(Math.random()*10000)}`,
        binding: f.binding || { binding_type: 'direct', target_entity: '', target_path: '' }
      }))
    }))
    isDirty.value = true
  }
})

window.addEventListener('ui-generation-failed', (event) => {
  isGenerating.value = false
  alert('Generation failed: ' + (event.detail[0]?.message || event.detail?.message || 'Unknown error'))
})

async function submitForReview() {
  if (isDirty.value) {
    if (!confirm('You have unsaved changes. Save now before submitting?')) return
    await savePage()
  }

  if (confirm('Submit this feature version for review? This will lock it for designers.')) {
    try {
      const res = await fetch(`/api/studio/versions/${props.versionId}/submit`, {
         method: 'POST',
         headers: { 
           'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
           'Content-Type': 'application/json'
         }
      })
      const data = await res.json()
      if (data.success) {
        alert('Submitted successfully!')
        window.location.href = '/studio/releases'
      } else {
        alert('Submission failed: ' + data.message)
      }
    } catch (error) {
      alert('Error submitting: ' + error.message)
    }
  }
}
</script>

<template>
  <div class="page-builder">
    <!-- Left: Field Library -->
    <aside class="mac-sidebar left">
      <div class="sidebar-header">
        <div class="mac-icon-box"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg></div>
        <span>Field Library</span>
      </div>

      <div class="sidebar-content">
        <div v-for="cat in fieldCategories" :key="cat.name" class="lib-cat">
          <label class="lib-cat-label">{{ cat.name }}</label>
          <div
            v-for="ft in cat.types"
            :key="ft.type"
            class="lib-field"
            draggable="true"
            @dragstart="(e) => onFieldDragStart(e, ft)"
          >
            <div class="lib-icon" v-html="ft.icon"></div>
            <span class="lib-label">{{ ft.label }}</span>
          </div>
        </div>
      </div>
    </aside>

    <!-- Center: Form Canvas -->
    <div class="form-canvas-area">
      <!-- Apple Island Toolbar -->
      <div class="canvas-island-toolbar">
        <div class="preview-toggles">
          <button v-for="mode in ['desktop', 'tablet', 'mobile']" 
            :key="mode" 
            class="island-btn segmented" 
            :class="{ active: previewMode === mode }"
            @click="previewMode = mode"
            :title="mode"
            v-html="mode === 'desktop' ? '<svg width=\'14\' height=\'14\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><rect x=\'2\' y=\'3\' width=\'20\' height=\'14\' rx=\'2\' ry=\'2\'></rect><line x1=\'8\' y1=\'21\' x2=\'16\' y2=\'21\'></line><line x1=\'12\' y1=\'17\' x2=\'12\' y2=\'21\'></line></svg>' : (mode === 'tablet' ? '<svg width=\'14\' height=\'14\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><rect x=\'4\' y=\'2\' width=\'16\' height=\'20\' rx=\'2\' ry=\'2\'></rect><line x1=\'12\' y1=\'18\' x2=\'12.01\' y2=\'18\'></line></svg>' : '<svg width=\'14\' height=\'14\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><rect x=\'5\' y=\'2\' width=\'14\' height=\'20\' rx=\'2\' ry=\'2\'></rect><line x1=\'12\' y1=\'18\' x2=\'12.01\' y2=\'18\'></line></svg>')"
          >
          </button>
        </div>
        
        <div class="island-divider"></div>

        <button class="island-btn" @click="showPreview = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          Preview
        </button>

        <div class="island-divider"></div>

        <button class="island-btn magic" @click="triggerAIGeneration" :disabled="isGenerating">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
          <span v-if="isGenerating">Building...</span>
          <span v-else>{{ steps.length > 0 ? 'Re-Gen UI' : 'Gen-UI' }}</span>
        </button>

        <button class="island-btn submit" @click="submitForReview">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          Submit Review
        </button>
      </div>

      <!-- Canvas Scrollable Content -->
      <div class="canvas-scroll">
        <!-- Step Tabs (Segmented Control Style) -->
        <div class="mac-segmented-control-wrapper">
          <div class="mac-segmented-control">
            <button
              v-for="(step, idx) in steps"
              :key="step.step_key"
              class="seg-btn"
              :class="{ active: idx === activeStepIdx }"
              @click="activeStepIdx = idx"
            >
              Step {{ idx + 1 }}
              <span v-if="steps.length > 1" class="seg-remove" @click.stop="removeStep(idx)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
              </span>
            </button>
            <button class="seg-btn add-btn" @click="addStep">+</button>
          </div>
        </div>

        <div class="canvas-content-bounds" :style="{ width: canvasWidth, margin: '0 auto', transition: 'width 0.3s cubic-bezier(0.2, 0.8, 0.2, 1)' }">
          
          <!-- Step Config Header -->
          <div v-if="activeStep" class="mac-card step-header-card">
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #86868b; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;">
               Feature: {{ featureName || 'Unknown' }} 
               <span v-if="flowName" style="color: #007aff; background: rgba(0,122,255,0.1); padding: 2px 6px; border-radius: 4px;">Flow Ref: {{ flowName }}</span>
            </div>
            <input v-model="activeStep.title" class="mac-input large header-input" placeholder="Page Title (e.g. Personal Details)" @change="isDirty = true" />
            <input v-model="activeStep.entity_binding" class="mac-input small mono" placeholder="entity (e.g. user_profile)" @change="isDirty = true" />
          </div>

          <!-- Drop Zone -->
          <div
            v-if="activeStep"
            class="mac-drop-zone"
            @drop="onFieldDrop"
            @dragover="onFieldDragOver"
          >
            <div v-if="activeStep.fields.length === 0" class="mac-empty-state">
              <div class="empty-icon">📋</div>
              <p>Drag UI components here to build your form</p>
            </div>

            <div
              v-for="(field, idx) in activeStep.fields"
              :key="field.field_key"
              class="mac-field-card"
              :class="{ selected: selectedField?.field_key === field.field_key }"
              draggable="true"
              @click="selectField(field)"
              @dragstart="(e) => onReorderStart(e, idx)"
              @drop.stop="(e) => onReorderDrop(e, idx)"
              @dragover.prevent
            >
              <div class="drag-handle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="4" y1="8" x2="20" y2="8"></line><line x1="4" y1="16" x2="20" y2="16"></line></svg>
              </div>
              <div class="field-preview-content">
                <label class="field-label">
                  {{ field.label }}
                  <span v-if="field.is_required" class="required-mark">*</span>
                </label>
                <div class="fake-input" :class="field.component_type">
                  {{ field.placeholder || 'Enter value...' }}
                </div>
              </div>
              <button class="remove-action" @click.stop="removeField(field.field_key)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
              </button>
            </div>
            
            <div class="drop-target-line"></div>
          </div>
        </div>
      </div>

      <!-- Floating Unsaved Ribbon -->
      <transition name="slide-up">
        <div v-if="isDirty" class="mac-floating-saver">
          <div class="saver-info">
            <span class="dot pulse"></span>
            Unsaved Changes
          </div>
          <button class="mac-btn primary small" @click="savePage">Save Now</button>
        </div>
      </transition>
    </div>

    <!-- Right: Field Inspector -->
    <aside class="mac-sidebar right" :class="{ 'is-empty': !selectedField }">
      <template v-if="selectedField">
        <div class="sidebar-header">
          <span>Field Inspector</span>
          <span class="mac-badge">{{ selectedField.component_type.replace('_', ' ') }}</span>
        </div>

        <div class="sidebar-content">
          <div class="mac-form-group">
            <label>Field Label</label>
            <input v-model="selectedField.label" class="mac-input" @change="updateField" />
          </div>
          <div class="mac-form-group">
            <label>Internal Key</label>
            <input v-model="selectedField.field_key" class="mac-input mono disabled" disabled />
          </div>
          <div class="mac-form-group">
            <label>Placeholder Text</label>
            <input v-model="selectedField.placeholder" class="mac-input" @change="updateField" />
          </div>
          <div class="mac-form-group">
            <label>Help Description</label>
            <textarea v-model="selectedField.help_text" class="mac-textarea" @change="updateField" rows="2"></textarea>
          </div>
          
          <div class="mac-toggle-group">
            <label class="mac-toggle">
              <input type="checkbox" v-model="selectedField.is_required" @change="updateField" />
              <div class="toggle-track"></div>
              <span class="toggle-label">Required Field</span>
            </label>
          </div>

          <div class="mac-divider">Validation Rules</div>
          
          <div class="mac-row-group">
            <div class="mac-form-group half">
              <label>Min Length</label>
              <input type="number" v-model="selectedField.config.min_length" class="mac-input" placeholder="0" @change="updateField" />
            </div>
            <div class="mac-form-group half">
              <label>Max Length</label>
              <input type="number" v-model="selectedField.config.max_length" class="mac-input" placeholder="255" @change="updateField" />
            </div>
          </div>
          <div class="mac-form-group">
            <label>Regex Pattern</label>
            <input v-model="selectedField.config.pattern" class="mac-input mono" placeholder="^[a-zA-Z]+$" @change="updateField" />
          </div>
          <div class="mac-form-group">
            <label>Error Message</label>
            <input v-model="selectedField.config.custom_error" class="mac-input" placeholder="Invalid format" @change="updateField" />
          </div>

          <div class="mac-divider">Data Binding</div>
          
          <div class="mac-form-group">
            <label>Target Entity</label>
            <div class="mac-select-wrapper">
              <select v-model="selectedField.binding.target_entity" class="mac-select" @change="updateField">
                <option value="">None</option>
                <option v-for="(fields, entity) in entities" :key="entity" :value="entity">{{ entity }}</option>
              </select>
              <svg class="select-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
          </div>
          <div class="mac-form-group" v-if="selectedField.binding.target_entity">
            <label>Entity Property Path</label>
            <div class="mac-select-wrapper">
              <select v-model="selectedField.binding.target_path" class="mac-select" @change="updateField">
                <option value="">Select Property...</option>
                <option v-for="fieldName in entities[selectedField.binding.target_entity]" :key="fieldName" :value="fieldName">
                  {{ fieldName }}
                </option>
              </select>
              <svg class="select-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
          </div>
        </div>
      </template>
      <template v-else>
        <div class="mac-empty-inspector">
          <div class="icon-ring">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
          </div>
          <p>Select a field to configure its properties</p>
        </div>
      </template>
    </aside>

    <!-- Full Page Preview Modal -->
    <PagePreviewModal
      :show="showPreview"
      :steps="steps"
      :page-name="activeStep?.title || 'New Page'"
      @close="showPreview = false"
    />
  </div>
</template>

<style scoped>
/* ─── Apple Design System Base ─── */
.page-builder {
  display: flex;
  height: 100vh;
  width: 100%;
  max-height: 100%;
  background: #f5f5f7; /* Apple grey backdrop */
  font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Inter", sans-serif;
  color: #1d1d1f;
  overflow: hidden;
}

/* ─── Shared Sidebars ─── */
.mac-sidebar {
  width: 280px;
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border-right: 1px solid rgba(0,0,0,0.06);
  display: flex;
  flex-direction: column;
  z-index: 10;
  flex-shrink: 0;
}
.mac-sidebar.right {
  border-right: none;
  border-left: 1px solid rgba(0,0,0,0.06);
}
.sidebar-header {
  height: 60px;
  padding: 0 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  border-bottom: 1px solid rgba(0,0,0,0.04);
  font-weight: 600;
  font-size: 14px;
  color: #1d1d1f;
  background: rgba(255, 255, 255, 0.5);
}
.mac-icon-box {
  width: 28px;
  height: 28px;
  background: rgba(0,0,0,0.04);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}
.sidebar-content {
  padding: 20px;
  flex: 1;
  overflow-y: auto;
}

/* ─── Field Library (Left) ─── */
.lib-cat {
  margin-bottom: 24px;
}
.lib-cat-label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #86868b;
  margin-bottom: 10px;
  padding-left: 4px;
}
.lib-field {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  border-radius: 10px;
  cursor: grab;
  margin-bottom: 4px;
  transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
  border: 1px solid transparent;
}
.lib-field:hover {
  background: #ffffff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  border-color: rgba(0,0,0,0.03);
  transform: translateX(2px);
}
.lib-field:active {
  cursor: grabbing;
  transform: scale(0.98);
}
.lib-icon {
  font-size: 18px;
}
.lib-label {
  font-size: 13px;
  font-weight: 500;
  color: #1d1d1f;
}

/* ─── Form Canvas Area (Center) ─── */
.form-canvas-area {
  flex: 1;
  position: relative;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Island Toolbar */
.canvas-island-toolbar {
  position: absolute;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  padding: 6px 8px;
  border-radius: 100px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.04);
  border: 1px solid rgba(0,0,0,0.05);
  z-index: 50;
}
.preview-toggles {
  display: flex;
  gap: 2px;
}
.island-btn {
  background: transparent;
  border: none;
  border-radius: 100px;
  padding: 6px 14px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #1d1d1f;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.2s ease;
}
.island-btn:hover { background: rgba(0,0,0,0.04); }
.island-btn.segmented { padding: 6px 12px; }
.island-btn.active { background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.island-divider { width: 1px; height: 18px; background: rgba(0,0,0,0.1); margin: 0 4px; }
.island-btn.primary:hover:not(:disabled) { background: #0066d6; }

.island-btn.submit { color: #007aff; }
.island-btn.submit:hover { background: rgba(0, 122, 255, 0.08); }

.island-btn.magic {
  color: #af52de;
  background: rgba(175, 82, 222, 0.08);
}
.island-btn.magic:hover:not(:disabled) {
  background: rgba(175, 82, 222, 0.15);
}

.island-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Canvas Scrollable Content */
.canvas-scroll {
  flex: 1;
  padding: 90px 40px 100px 40px;
  overflow-y: auto;
  overflow-x: hidden;
  position: relative;
}

/* Segmented Control (Steps) */
.mac-segmented-control-wrapper {
  display: flex;
  justify-content: center;
  margin-bottom: 30px;
}
.mac-segmented-control {
  display: inline-flex;
  background: rgba(0,0,0,0.06);
  padding: 3px;
  border-radius: 9px;
  gap: 2px;
}
.seg-btn {
  border: none;
  background: transparent;
  padding: 6px 16px;
  font-size: 13px;
  font-weight: 500;
  color: #1d1d1f;
  border-radius: 7px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.seg-btn:hover { background: rgba(0,0,0,0.04); }
.seg-btn.active {
  background: #ffffff;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
}
.seg-remove {
  color: #86868b;
  display: flex;
  padding: 2px;
  border-radius: 10px;
}
.seg-remove:hover { color: #ff3b30; background: rgba(255,59,48,0.1); }
.seg-btn.add-btn { color: #86868b; padding: 6px 12px; font-weight: 600; }

/* Canvas Bounds & Headers */
.canvas-content-bounds {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.mac-card {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.03), 0 1px 3px rgba(0,0,0,0.02);
  border: 1px solid rgba(0,0,0,0.04);
  padding: 24px;
}
.step-header-card {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.mac-input {
  width: 100%;
  background: #f5f5f7;
  border: 1px solid transparent;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 13px;
  color: #1d1d1f;
  transition: all 0.2s ease;
  outline: none;
  font-family: inherit;
}
.mac-input:focus, .mac-textarea:focus {
  background: #ffffff;
  border-color: #007aff;
  box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2);
}
.mac-input.large { font-size: 20px; font-weight: 600; padding: 12px 16px; background: transparent; border: 1px solid #e5e5ea; }
.mac-input.large:focus { border-color: #007aff; background: #ffffff; }
.mac-input.small { padding: 8px 12px; font-size: 12px; }
.mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

/* Drop Zone */
.mac-drop-zone {
  min-height: 400px;
  background: #ffffff;
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.02);
  border: 1px solid rgba(0,0,0,0.04);
}
.mac-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 300px;
  color: #86868b;
  font-size: 14px;
  border: 2px dashed #e5e5ea;
  border-radius: 12px;
  background: #fafafa;
}
.mac-empty-state .empty-icon { font-size: 40px; margin-bottom: 12px; opacity: 0.5; }

/* Field Cards */
.mac-field-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px;
  margin-bottom: 12px;
  background: #ffffff;
  border: 1px solid #e5e5ea;
  border-radius: 14px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
  box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.mac-field-card:hover {
  border-color: #d1d1d6;
  box-shadow: 0 6px 16px rgba(0,0,0,0.06);
  transform: translateY(-1px);
}
.mac-field-card.selected {
  border-color: #007aff;
  box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.15), 0 6px 16px rgba(0,0,0,0.06);
}
.drag-handle {
  color: #c7c7cc;
  cursor: grab;
  padding: 4px;
  margin-top: 2px;
}
.drag-handle:active { cursor: grabbing; }
.field-preview-content {
  flex: 1;
}
.field-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 8px;
}
.required-mark { color: #ff3b30; margin-left: 4px; }
.fake-input {
  background: #f5f5f7;
  border: 1px solid rgba(0,0,0,0.06);
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 13px;
  color: #86868b;
  user-select: none;
}
.remove-action {
  background: transparent;
  border: none;
  color: #c7c7cc;
  padding: 6px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}
.remove-action:hover { color: #ff3b30; background: rgba(255,59,48,0.1); }

/* Floating Save Ribbon */
.mac-floating-saver {
  position: absolute;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  padding: 10px 14px 10px 20px;
  border-radius: 100px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.12);
  border: 1px solid rgba(0,0,0,0.05);
  display: flex;
  align-items: center;
  gap: 20px;
  z-index: 50;
}
.saver-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #1d1d1f;
}
.dot { width: 8px; height: 8px; background: #ff9500; border-radius: 50%; }
.pulse { animation: pulseAnim 2s infinite; }
@keyframes pulseAnim {
  0% { box-shadow: 0 0 0 0 rgba(255, 149, 0, 0.4); }
  70% { box-shadow: 0 0 0 6px rgba(255, 149, 0, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 149, 0, 0); }
}

/* Base Buttons */
.mac-btn {
  border: none;
  background: #ffffff;
  padding: 10px 18px;
  border-radius: 100px;
  font-size: 14px;
  font-weight: 500;
  color: #1d1d1f;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.mac-btn:hover { box-shadow: 0 2px 6px rgba(0,0,0,0.12); transform: translateY(-1px); }
.mac-btn:active { transform: translateY(0); }
.island-btn.primary {
  background: #007aff;
  color: white;
}
.island-btn.primary:hover:not(:disabled) { background: #005bb5; }
.island-btn.magic {
  background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(236, 72, 153, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
.island-btn.magic:hover:not(:disabled) {
  background: linear-gradient(135deg, #9333ea 0%, #db2777 100%);
  box-shadow: 0 6px 16px rgba(236, 72, 153, 0.35);
}
.mac-btn.primary { background: #007aff; color: #ffffff; }
.mac-btn.primary:hover { background: #0066d6; box-shadow: 0 4px 12px rgba(0, 122, 255, 0.3); }
.mac-btn.small { padding: 6px 14px; font-size: 12px; }

/* ─── Field Inspector (Right) ─── */
.mac-badge {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: rgba(0, 122, 255, 0.1);
  color: #007aff;
  padding: 4px 8px;
  border-radius: 6px;
}
.mac-form-group {
  margin-bottom: 16px;
}
.mac-row-group { display: flex; gap: 12px; }
.half { flex: 1; }
.mac-form-group label {
  display: block;
  font-size: 11px;
  font-weight: 500;
  color: #86868b;
  margin-bottom: 6px;
}
.mac-textarea {
  width: 100%;
  background: #f5f5f7;
  border: 1px solid transparent;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 13px;
  color: #1d1d1f;
  transition: all 0.2s ease;
  outline: none;
  font-family: inherit;
  resize: vertical;
}
.mac-input.disabled { opacity: 0.6; cursor: not-allowed; }

/* Custom Select */
.mac-select-wrapper { position: relative; }
.mac-select {
  width: 100%;
  background: #f5f5f7;
  border: 1px solid transparent;
  border-radius: 10px;
  padding: 10px 32px 10px 14px;
  font-size: 13px;
  color: #1d1d1f;
  appearance: none;
  cursor: pointer;
  outline: none;
  font-family: inherit;
}
.mac-select:focus { background: #ffffff; border-color: #007aff; box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2); }
.select-icon {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  width: 16px; height: 16px; color: #86868b; pointer-events: none;
}

/* Custom Toggle Switch */
.mac-toggle-group { margin: 20px 0; }
.mac-toggle {
  display: flex; align-items: center; gap: 12px; cursor: pointer;
  position: relative;
}
.mac-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
.toggle-track {
  width: 44px; height: 24px; background: #e5e5ea; border-radius: 24px;
  position: relative; transition: background 0.3s;
}
.toggle-track::after {
  content: ''; position: absolute; top: 2px; left: 2px; width: 20px; height: 20px;
  background: #ffffff; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  transition: transform 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.mac-toggle input:checked + .toggle-track { background: #34c759; }
.mac-toggle input:checked + .toggle-track::after { transform: translateX(20px); }
.toggle-label { font-size: 13px; font-weight: 500; color: #1d1d1f; }

/* Divider */
.mac-divider {
  font-size: 11px; font-weight: 600; text-transform: uppercase;
  color: #86868b; margin: 24px 0 16px;
  padding-top: 16px; border-top: 1px solid rgba(0,0,0,0.06);
}

/* Empty State */
.mac-empty-inspector {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  height: 100%; color: #86868b; text-align: center; padding: 20px;
}
.icon-ring {
  width: 64px; height: 64px; border-radius: 50%; background: #f5f5f7;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 16px; color: #c7c7cc;
}
.icon-ring svg { width: 32px; height: 32px; }

/* Transitions */
.slide-up-enter-active, .slide-up-leave-active { transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1); }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translate(-50%, 20px); }
</style>
