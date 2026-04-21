<script setup>
/**
 * PagePreviewModal — Renders a live preview of the designed form steps.
 * Provides a mini "runtime" experience for HQ designers.
 */
import { ref, computed } from 'vue'

const props = defineProps({
  show: Boolean,
  steps: { type: Array, default: () => [] },
  pageName: String
})

const emit = defineEmits(['close'])

const currentStepIdx = ref(0)
const activeStep = computed(() => props.steps[currentStepIdx.value] || null)

function nextStep() {
  if (currentStepIdx.value < props.steps.length - 1) {
    currentStepIdx.value++
  }
}

function prevStep() {
  if (currentStepIdx.value > 0) {
    currentStepIdx.value--
  }
}

// Light theme color palette (Apple inspired tones)
const themeColors = [
    '#007aff', // Blue
    '#34c759', // Green
    '#ff3b30', // Red
    '#af52de', // Purple
    '#ff9500', // Orange
    '#1d1d1f'  // Dark/Graphite
]
const currentColor = ref(themeColors[0])

const themeColorAlpha = computed(() => {
    // Generate an RGBA string for shadows based on the hex
    const hex = currentColor.value.replace('#', '')
    const r = parseInt(hex.substring(0, 2), 16)
    const g = parseInt(hex.substring(2, 4), 16)
    const b = parseInt(hex.substring(4, 6), 16)
    return `rgba(${r}, ${g}, ${b}, 0.25)`
})
</script>

<template>
  <div v-if="show" class="preview-overlay" @click.self="emit('close')">
    <div class="preview-modal" :style="{ '--theme-color': currentColor, '--theme-color-alpha': themeColorAlpha }">
      <header class="modal-header">
        <div class="header-left">
          <span class="preview-badge">PREVIEW</span>
          <h3>{{ pageName || 'Untitled Page' }}</h3>
        </div>
        <div class="header-right">
            <span class="theme-label">Accent Color:</span>
            <div class="color-picker">
                <button 
                    v-for="color in themeColors" 
                    :key="color" 
                    class="color-swatch"
                    :class="{ active: currentColor === color }"
                    :style="{ backgroundColor: color }"
                    title="Change Theme Color"
                    @click="currentColor = color"
                ></button>
            </div>
            <div class="divider"></div>
            <button class="close-btn" @click="emit('close')" title="Close Preview">×</button>
        </div>
      </header>

      <div class="modal-body">
        <!-- Progress Stepper -->
        <nav class="preview-stepper">
          <div 
            v-for="(step, idx) in steps" 
            :key="idx" 
            class="step-indicator"
            :class="{ active: idx === currentStepIdx, completed: idx < currentStepIdx }"
          >
            <div class="circle">
                <svg v-if="idx < currentStepIdx" class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span v-else>{{ idx + 1 }}</span>
            </div>
            <span class="label">{{ step.title }}</span>
            <div v-if="idx < steps.length - 1" class="line"></div>
          </div>
        </nav>

        <!-- Current Step Content -->
        <div v-if="activeStep" class="step-content">
          <div class="step-intro">
            <h4>{{ activeStep.title }}</h4>
            <p v-if="activeStep.description">{{ activeStep.description }}</p>
          </div>

          <div class="fields-grid">
            <div 
              v-for="field in activeStep.fields" 
              :key="field.field_key" 
              class="preview-field"
            >
              <label>
                {{ field.label }}
                <span v-if="field.is_required" class="required">*</span>
              </label>

              <!-- Mock Input Rendering -->
              <div class="mock-input" :class="field.component_type">
                <template v-if="field.component_type === 'textarea'">
                  <textarea disabled :placeholder="field.placeholder || 'Enter text here...'"></textarea>
                </template>
                <template v-else-if="field.component_type === 'select'">
                  <select disabled>
                    <option>{{ field.placeholder || 'Select option...' }}</option>
                  </select>
                </template>
                <template v-else-if="['checkbox', 'radio'].includes(field.component_type)">
                  <div class="mock-inline-group">
                      <div class="check-mock" :class="field.component_type"></div>
                      <span>{{ field.placeholder || 'Option Label' }}</span>
                  </div>
                </template>
                <template v-else>
                  <input type="text" disabled :placeholder="field.placeholder || 'Enter value...'" />
                </template>
              </div>
              <p v-if="field.help_text" class="help-text">{{ field.help_text }}</p>
            </div>
            <div v-if="activeStep.fields.length === 0" class="empty-fields">
                <p>No fields added to this step.</p>
            </div>
          </div>
        </div>
      </div>

      <footer class="modal-footer">
        <button 
          class="btn-secondary" 
          :disabled="currentStepIdx === 0" 
          @click="prevStep"
        >
          Previous
        </button>
        <div class="flex-spacer"></div>
        <button 
          v-if="currentStepIdx < steps.length - 1" 
          class="btn-primary" 
          @click="nextStep"
        >
          Next Step
        </button>
        <button v-else class="btn-primary" @click="emit('close')">
          Finish Review
        </button>
      </footer>
    </div>
  </div>
</template>

<style scoped>
.preview-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.preview-modal {
  width: 100%;
  max-width: 800px;
  max-height: 90vh;
  background: #ffffff; /* Light Modded Background */
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 20px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0,0,0,0.05);
  font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Inter", sans-serif;
  transition: all 0.3s ease;
}

.modal-header {
  padding: 16px 24px;
  background: #fcfcfc;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.header-left { display: flex; align-items: center; gap: 12px; }
.preview-badge {
  font-size: 10px; font-weight: 700; padding: 3px 8px;
  background: var(--theme-color); color: #fff; border-radius: 6px;
  letter-spacing: 0.05em;
}
.modal-header h3 { margin: 0; font-size: 16px; font-weight: 600; color: #1d1d1f; }

.header-right {
    display: flex; align-items: center; gap: 12px;
}
.theme-label {
    font-size: 11px; font-weight: 500; color: #86868b;
}
.color-picker {
    display: flex; gap: 6px;
    background: #f1f5f9; padding: 4px; border-radius: 100px;
}
.color-swatch {
    width: 20px; height: 20px; border-radius: 50%;
    border: 2px solid white; cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: transform 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.color-swatch:hover {
    transform: scale(1.1);
}
.color-swatch.active {
    transform: scale(1.2);
    box-shadow: 0 0 0 2px var(--theme-color);
}
.divider { width: 1px; height: 20px; background: #e2e8f0; margin: 0 4px; }
.close-btn { 
    background: #f1f5f9; border: none; color: #64748b; 
    width: 32px; height: 32px; border-radius: 50%; 
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; font-weight: 500; cursor: pointer; transition: all 0.2s;
}
.close-btn:hover { background: #e2e8f0; color: #1e293b; }

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 40px;
}

.preview-stepper {
  display: flex;
  justify-content: center;
  gap: 0;
  margin-bottom: 48px;
  padding: 0 40px;
}

.step-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  flex: 1;
  position: relative;
}

.circle {
  width: 32px; height: 32px; border-radius: 50%;
  background: #f8fafc; color: #94a3b8;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 600; border: 2px solid #e2e8f0;
  z-index: 2; transition: all 0.3s ease;
}
.check-icon { width: 14px; height: 14px; }

.step-indicator.active .circle {
  background: var(--theme-color); color: #fff;
  border-color: var(--theme-color);
  box-shadow: 0 0 0 4px var(--theme-color-alpha);
  transform: scale(1.1);
}

.step-indicator.completed .circle {
  background: var(--theme-color); color: #fff;
  border-color: var(--theme-color);
}

.label { font-size: 12px; font-weight: 600; color: #94a3b8; text-align: center; transition: color 0.3s; }
.step-indicator.active .label { color: var(--theme-color); }
.step-indicator.completed .label { color: #1d1d1f; }

.line {
  position: absolute;
  top: 16px;
  left: 50%;
  width: 100%;
  height: 2px;
  background: #e2e8f0;
  z-index: 1;
  transition: background 0.3s ease;
}
.step-indicator.completed .line { background: var(--theme-color); }

.step-content {
  max-width: 500px;
  margin: 0 auto;
}

.step-intro { margin-bottom: 32px; text-align: center; }
.step-intro h4 { margin: 0 0 8px; font-size: 22px; font-weight: 600; color: #1d1d1f; letter-spacing: -0.02em; }
.step-intro p { margin: 0; color: #64748b; font-size: 14px; }

.fields-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
}

.preview-field label {
  display: block; font-size: 13px; font-weight: 600; 
  color: #475569; margin-bottom: 8px;
}
.required { color: #ff3b30; margin-left: 2px; }

.mock-input input, .mock-input textarea, .mock-input select {
  width: 100%; padding: 12px 16px;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 12px; color: #1e293b;
  font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);
  transition: all 0.2s;
  font-family: inherit;
}
.mock-input select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-size: 16px; background-position: calc(100% - 16px) center; background-repeat: no-repeat; }
.mock-input input:disabled, .mock-input textarea:disabled, .mock-input select:disabled { background: #f8fafc; cursor: not-allowed; }

.mock-inline-group { display: flex; align-items: center; gap: 10px; padding: 8px 0; }
.check-mock {
  width: 20px; height: 20px; border-radius: 6px;
  border: 2px solid #cbd5e1;
  background: #ffffff;
}
.check-mock.radio { border-radius: 50%; }
.mock-inline-group span { font-size: 14px; color: #334155; }

.preview-field .help-text {
  margin: 6px 0 0; font-size: 12px; color: #94a3b8;
}

.empty-fields { text-align: center; padding: 40px; border: 2px dashed #e2e8f0; border-radius: 16px; color: #94a3b8; font-size: 14px; }

.modal-footer {
  padding: 20px 32px;
  background: #fcfcfc;
  border-top: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
}

.flex-spacer { flex: 1; }

.btn-primary {
  background: var(--theme-color); color: #fff; border: none;
  padding: 12px 28px; border-radius: 10px; font-weight: 600; font-size: 14px;
  cursor: pointer; box-shadow: 0 4px 12px var(--theme-color-alpha);
  transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.btn-primary:active { transform: scale(0.98); }
.btn-primary:hover { filter: brightness(0.95); }

.btn-secondary {
  background: #f1f5f9; color: #475569; border: none;
  padding: 12px 28px; border-radius: 10px; font-weight: 600; font-size: 14px;
  cursor: pointer; transition: background 0.2s;
}
.btn-secondary:hover:not(:disabled) { background: #e2e8f0; color: #1e293b; }
.btn-secondary:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
