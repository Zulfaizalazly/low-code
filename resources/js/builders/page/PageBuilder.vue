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
      { type: 'text_input', label: 'Text Input', icon: '📝', dataType: 'string' },
      { type: 'amount_input', label: 'Currency', icon: '💰', dataType: 'decimal' },
      { type: 'date_picker', label: 'Date Picker', icon: '📅', dataType: 'date' },
      { type: 'select', label: 'Dropdown', icon: '📋', dataType: 'string' },
      { type: 'textarea', label: 'Text Area', icon: '📄', dataType: 'text' },
      { type: 'checkbox', label: 'Checkbox', icon: '☑️', dataType: 'boolean' },
      { type: 'radio', label: 'Radio Group', icon: '⭕', dataType: 'string' },
      { type: 'file_upload', label: 'File Upload', icon: '📁', dataType: 'file' },
    ]
  },
  {
    name: 'Domain Specific',
    types: [
      { type: 'ic_input', label: 'IC Number', icon: '🆔', dataType: 'string' },
      { type: 'phone_input', label: 'Phone Number', icon: '📱', dataType: 'string' },
      { type: 'gold_repeater', label: 'Gold Items', icon: '💍', dataType: 'collection' },
      { type: 'nominee_repeater', label: 'Nominees', icon: '👥', dataType: 'collection' },
    ]
  },
  {
    name: 'Display & Layout',
    types: [
      { type: 'summary_panel', label: 'Summary Panel', icon: '📊', dataType: 'display' },
      { type: 'timeline', label: 'Timeline', icon: '⏳', dataType: 'display' },
      { type: 'alert', label: 'Alert Banner', icon: '⚠️', dataType: 'display' },
      { type: 'badge', label: 'Status Badge', icon: '🏷️', dataType: 'display' },
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
function savePage() {
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

  window.dispatchEvent(new CustomEvent('vue-page-save', {
    detail: { steps: serialized }
  }))
  isDirty.value = false
}

window.savePageToLivewire = savePage

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
    <aside class="field-library">
      <div class="lib-header">
        <span>🧱</span>
        <span>Field Library</span>
      </div>

      <div v-for="cat in fieldCategories" :key="cat.name" class="lib-cat">
        <label class="lib-cat-label">{{ cat.name }}</label>
        <div
          v-for="ft in cat.types"
          :key="ft.type"
          class="lib-field"
          draggable="true"
          @dragstart="(e) => onFieldDragStart(e, ft)"
        >
          <span class="lib-icon">{{ ft.icon }}</span>
          <span class="lib-label">{{ ft.label }}</span>
        </div>
      </div>
    </aside>

    <!-- Center: Form Canvas -->
    <div class="form-canvas">
      <!-- Canvas Header -->
      <div class="canvas-header">
        <div class="preview-toggles">
          <button v-for="mode in ['desktop', 'tablet', 'mobile']" 
            :key="mode" 
            class="mode-btn" 
            :class="{ active: previewMode === mode }"
            @click="previewMode = mode"
          >
            {{ mode === 'desktop' ? '💻' : (mode === 'tablet' ? '📱' : '📲') }}
            {{ mode }}
          </button>
        </div>
        <div class="canvas-actions">
          <button class="preview-btn" @click="showPreview = true" style="margin-right: 8px;">👁️ Preview Page</button>
          <button 
            @click="submitForReview" 
            class="submit-btn"
          >
            🕵️ Submit for Review
          </button>
        </div>
      </div>

      <!-- Step Tabs -->
      <div class="step-tabs">
        <button
          v-for="(step, idx) in steps"
          :key="step.step_key"
          class="step-tab"
          :class="{ active: idx === activeStepIdx }"
          @click="activeStepIdx = idx"
        >
          <span class="step-num">{{ idx + 1 }}</span>
          {{ step.title }}
          <span v-if="steps.length > 1" class="step-remove" @click.stop="removeStep(idx)">×</span>
        </button>
        <button class="step-tab add-step" @click="addStep">+ Add Step</button>
      </div>

      <!-- Step Config -->
      <div v-if="activeStep" class="step-config">
        <input v-model="activeStep.title" class="step-title-input" placeholder="Step title..." @change="isDirty = true" />
        <input v-model="activeStep.entity_binding" class="step-entity-input" placeholder="Entity binding (e.g. facility_nominees)" @change="isDirty = true" />
      </div>

      <!-- Drop Zone -->
      <div
        v-if="activeStep"
        class="field-drop-zone"
        :style="{ width: canvasWidth, margin: '0 auto', transition: 'width 0.3s ease' }"
        @drop="onFieldDrop"
        @dragover="onFieldDragOver"
      >
        <div v-if="activeStep.fields.length === 0" class="drop-placeholder">
          <span>📋</span>
          <p>Drag fields from the library to add them here</p>
        </div>

        <div
          v-for="(field, idx) in activeStep.fields"
          :key="field.field_key"
          class="field-card"
          :class="{ selected: selectedField?.field_key === field.field_key }"
          draggable="true"
          @click="selectField(field)"
          @dragstart="(e) => onReorderStart(e, idx)"
          @drop.stop="(e) => onReorderDrop(e, idx)"
          @dragover.prevent
        >
          <div class="field-drag-handle">⠿</div>
          <div class="field-preview">
            <label class="field-preview-label">
              {{ field.label }}
              <span v-if="field.is_required" class="required-dot">*</span>
            </label>
            <div class="field-preview-input" :class="field.component_type">
              {{ field.placeholder || field.component_type }}
            </div>
          </div>
          <button class="field-remove" @click.stop="removeField(field.field_key)">×</button>
        </div>
      </div>

      <!-- Dirty Indicator -->
      <div v-if="isDirty" class="dirty-bar">
        <span>● Unsaved changes</span>
        <button class="save-btn" @click="savePage">💾 Save Page</button>
      </div>
    </div>

    <!-- Right: Field Inspector -->
    <aside class="field-inspector" v-if="selectedField">
      <div class="insp-header">
        <h3>Field Properties</h3>
        <span class="type-badge">{{ selectedField.component_type }}</span>
      </div>

      <div class="insp-field">
        <label>Label</label>
        <input v-model="selectedField.label" @change="updateField" />
      </div>
      <div class="insp-field">
        <label>Key</label>
        <input v-model="selectedField.field_key" disabled />
      </div>
      <div class="insp-field">
        <label>Placeholder</label>
        <input v-model="selectedField.placeholder" @change="updateField" />
      </div>
      <div class="insp-field">
        <label>Help Text</label>
        <input v-model="selectedField.help_text" @change="updateField" />
      </div>
      <div class="insp-field insp-check">
        <label>
          <input type="checkbox" v-model="selectedField.is_required" @change="updateField" />
          Required
        </label>
      </div>

      <div class="insp-divider">Validation Rules</div>
      <div class="insp-field">
        <label>Min Length</label>
        <input type="number" v-model="selectedField.config.min_length" placeholder="0" @change="updateField" />
      </div>
      <div class="insp-field">
        <label>Max Length</label>
        <input type="number" v-model="selectedField.config.max_length" placeholder="255" @change="updateField" />
      </div>
      <div class="insp-field">
        <label>Pattern (Regex)</label>
        <input v-model="selectedField.config.pattern" placeholder="^[a-zA-Z]+$" @change="updateField" />
      </div>
      <div class="insp-field">
        <label>Custom Error Message</label>
        <input v-model="selectedField.config.custom_error" placeholder="Invalid input" @change="updateField" />
      </div>

      <div class="insp-divider">Data Binding</div>
      <div class="insp-field">
        <label>Target Entity</label>
        <select v-model="selectedField.binding.target_entity" @change="updateField" class="insp-select">
          <option value="">Select Entity...</option>
          <option v-for="(fields, entity) in entities" :key="entity" :value="entity">{{ entity }}</option>
        </select>
      </div>
      <div class="insp-field" v-if="selectedField.binding.target_entity">
        <label>Target Path</label>
        <select v-model="selectedField.binding.target_path" @change="updateField" class="insp-select">
          <option value="">Select Field...</option>
          <option v-for="fieldName in entities[selectedField.binding.target_entity]" :key="fieldName" :value="fieldName">
            {{ fieldName }}
          </option>
        </select>
      </div>
    </aside>

    <aside class="field-inspector empty" v-else>
      <div class="empty-state">
        <span>👆</span>
        <p>Select a field to edit</p>
      </div>
    </aside>

    <!-- Full Page Preview Modal -->
    <PagePreviewModal
      :show="showPreview"
      :steps="steps"
      :page-name="pageName || 'New Page'"
      @close="showPreview = false"
    />
  </div>
</template>

<style scoped>
.page-builder {
  display: flex;
  height: 100%;
  width: 100%;
  background: #0f172a;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(255,255,255,0.05);
}

/* ─── Field Library ─── */
.field-library {
  width: 180px;
  background: rgba(15, 23, 42, 0.6);
  border-right: 1px solid rgba(255,255,255,0.05);
  padding: 16px 10px;
  flex-shrink: 0;
}
.lib-header {
  display: flex; align-items: center; gap: 6px;
  font-size: 13px; font-weight: 600; color: #e2e8f0;
  margin-bottom: 20px; padding-bottom: 10px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.lib-cat {
  margin-bottom: 16px;
}
.lib-cat-label {
  display: block; font-size: 10px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 1px;
  color: #64748b; margin-bottom: 8px; padding-left: 4px;
}
.lib-field {
  display: flex; align-items: center; gap: 8px;
  padding: 7px 8px; border-radius: 8px;
  cursor: grab; margin-bottom: 3px;
  font-size: 12px; color: #cbd5e1;
  transition: background 0.15s, transform 0.1s;
}
.lib-field:hover { background: rgba(99,102,241,0.08); transform: translateX(2px); }
.lib-field:active { cursor: grabbing; }
.lib-icon { font-size: 15px; }

/* ─── Form Canvas ─── */
.form-canvas {
  flex: 1; padding: 20px; display: flex;
  flex-direction: column; min-height: 600px;
  overflow-x: auto;
}

.canvas-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 20px; padding-bottom: 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.preview-toggles {
  display: flex; gap: 4px; background: rgba(255,255,255,0.05);
  padding: 4px; border-radius: 10px;
}
.mode-btn {
  padding: 6px 12px; border-radius: 7px; border: none;
  background: transparent; color: #64748b; font-size: 11px;
  font-weight: 600; cursor: pointer; transition: all 0.2s;
  text-transform: capitalize;
}
.mode-btn.active { background: #334155; color: #e2e8f0; }

.preview-btn {
  padding: 8px 16px; background: rgba(99,102,241,0.1);
  border: 1px solid rgba(99,102,241,0.2); border-radius: 10px;
  color: #a5b4fc; font-size: 12px; font-weight: 600;
  cursor: pointer; transition: all 0.2s;
}
.preview-btn:hover { background: rgba(99,102,241,0.2); border-color: rgba(99,102,241,0.4); }

.submit-btn {
  padding: 8px 16px; background: #1e3a8a;
  border: 1px solid #3b82f6; border-radius: 10px;
  color: white; font-size: 12px; font-weight: 600;
  cursor: pointer; transition: all 0.2s;
}
.submit-btn:hover { background: #1e40af; box-shadow: 0 0 15px rgba(59, 130, 246, 0.4); }

.step-tabs {
  display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap;
}
.step-tab {
  display: flex; align-items: center; gap: 6px;
  padding: 6px 14px; border-radius: 8px;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(255,255,255,0.03); color: #94a3b8;
  font-size: 12px; cursor: pointer; transition: all 0.15s;
}
.step-tab.active {
  background: rgba(99,102,241,0.12);
  border-color: rgba(99,102,241,0.3);
  color: #a5b4fc;
}
.step-tab.add-step {
  border-style: dashed; color: #64748b;
}
.step-tab.add-step:hover { color: #a5b4fc; }
.step-num {
  width: 20px; height: 20px; display: flex; align-items: center;
  justify-content: center; border-radius: 6px; font-size: 10px;
  font-weight: 700; background: rgba(255,255,255,0.06);
}
.step-remove {
  font-size: 14px; opacity: 0.4; cursor: pointer;
  margin-left: 2px; line-height: 1;
}
.step-remove:hover { opacity: 1; color: #f87171; }

.step-config {
  display: flex; gap: 10px; margin-bottom: 14px;
}
.step-title-input, .step-entity-input {
  padding: 6px 10px; background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08); border-radius: 8px;
  color: #e2e8f0; font-size: 12px; outline: none;
}
.step-title-input { flex: 1; }
.step-entity-input { flex: 1.5; font-family: monospace; font-size: 11px; }

.field-drop-zone {
  flex: 1; border: 2px dashed rgba(255,255,255,0.06);
  border-radius: 12px; padding: 16px;
  min-height: 300px; overflow-y: auto;
}

.drop-placeholder {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; height: 200px; color: #475569;
  font-size: 13px;
}
.drop-placeholder span { font-size: 32px; margin-bottom: 8px; opacity: 0.5; }

.field-card {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 14px; border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.06);
  background: rgba(255,255,255,0.02);
  margin-bottom: 6px; cursor: pointer; transition: all 0.15s;
}
.field-card:hover { border-color: rgba(99,102,241,0.2); }
.field-card.selected {
  border-color: rgba(99,102,241,0.4);
  background: rgba(99,102,241,0.05);
}
.field-drag-handle {
  cursor: grab; color: #475569; font-size: 14px;
  letter-spacing: -1px; user-select: none;
}
.field-preview { flex: 1; }
.field-preview-label {
  font-size: 11px; font-weight: 600; color: #94a3b8;
  margin-bottom: 4px; display: block;
}
.required-dot { color: #ef4444; }
.field-preview-input {
  padding: 5px 8px; background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06); border-radius: 6px;
  font-size: 11px; color: #64748b;
}
.field-remove {
  background: none; border: none; color: #475569;
  font-size: 16px; cursor: pointer; padding: 2px 6px;
}
.field-remove:hover { color: #f87171; }

.dirty-bar {
  display: flex; justify-content: space-between; align-items: center;
  padding: 8px 14px; background: rgba(99,102,241,0.08);
  border: 1px solid rgba(99,102,241,0.15); border-radius: 10px;
  margin-top: 12px; font-size: 11px; color: #a5b4fc;
}
.save-btn {
  padding: 5px 14px; background: #6366f1; color: white;
  border: none; border-radius: 8px; font-size: 11px;
  font-weight: 600; cursor: pointer; transition: background 0.15s;
}
.save-btn:hover { background: #4f46e5; }

/* ─── Field Inspector ─── */
.field-inspector {
  width: 260px; background: rgba(15,23,42,0.6);
  border-left: 1px solid rgba(255,255,255,0.05);
  padding: 16px; flex-shrink: 0; overflow-y: auto;
}
.field-inspector.empty {
  display: flex; align-items: center; justify-content: center;
}
.insp-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 16px; padding-bottom: 10px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.insp-header h3 { font-size: 13px; font-weight: 600; color: #e2e8f0; margin: 0; }
.type-badge {
  font-size: 9px; padding: 2px 7px;
  background: rgba(99,102,241,0.15); color: #a5b4fc;
  border-radius: 6px; font-weight: 600; text-transform: uppercase;
}
.insp-field { margin-bottom: 12px; }
.insp-field label {
  display: block; font-size: 10px; font-weight: 600;
  color: #94a3b8; margin-bottom: 4px;
  text-transform: uppercase; letter-spacing: 0.5px;
}
.insp-field input[type="text"], .insp-field input:not([type]) {
  width: 100%; padding: 7px 9px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 7px; color: #e2e8f0; font-size: 12px;
  outline: none; box-sizing: border-box;
}
.insp-field input:focus { border-color: rgba(99,102,241,0.4); }
.insp-field input:disabled { opacity: 0.5; }
.insp-check label {
  display: flex; align-items: center; gap: 6px;
  font-size: 12px; color: #cbd5e1; cursor: pointer;
  text-transform: none; letter-spacing: 0;
}
.insp-divider {
  font-size: 10px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 1.5px; color: #64748b; margin: 16px 0 10px;
  padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.06);
}
.empty-state {
  text-align: center; color: #475569; font-size: 13px;
}
.empty-state span { font-size: 28px; display: block; margin-bottom: 8px; opacity: 0.5; }
</style>
