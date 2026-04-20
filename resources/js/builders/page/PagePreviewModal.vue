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
</script>

<template>
  <div v-if="show" class="preview-overlay" @click.self="emit('close')">
    <div class="preview-modal">
      <header class="modal-header">
        <div class="header-left">
          <span class="preview-badge">PREVIEW</span>
          <h3>{{ pageName || 'Untitled Page' }}</h3>
        </div>
        <button class="close-btn" @click="emit('close')">×</button>
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
            <div class="circle">{{ idx + 1 }}</div>
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
                  <textarea disabled :placeholder="field.placeholder"></textarea>
                </template>
                <template v-else-if="field.component_type === 'select'">
                  <select disabled>
                    <option>{{ field.placeholder || 'Select option...' }}</option>
                  </select>
                </template>
                <template v-else-if="['checkbox', 'radio'].includes(field.component_type)">
                  <div class="check-mock"></div>
                  <span>{{ field.placeholder || 'Option Label' }}</span>
                </template>
                <template v-else>
                  <input type="text" disabled :placeholder="field.placeholder" />
                </template>
              </div>
              <p v-if="field.help_text" class="help-text">{{ field.help_text }}</p>
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
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(10px);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.preview-modal {
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  background: #0f172a;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 24px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.modal-header {
  padding: 16px 24px;
  background: rgba(255, 255, 255, 0.03);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.header-left { display: flex; align-items: center; gap: 12px; }
.preview-badge {
  font-size: 10px; font-weight: 800; padding: 2px 6px;
  background: #fbbf24; color: #000; border-radius: 4px;
}
.modal-header h3 { margin: 0; font-size: 16px; color: #f1f5f9; }
.close-btn { background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer; }

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 32px;
}

.preview-stepper {
  display: flex;
  justify-content: center;
  gap: 0;
  margin-bottom: 40px;
  padding: 0 40px;
}

.step-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  flex: 1;
  position: relative;
}

.circle {
  width: 28px; height: 28px; border-radius: 50%;
  background: #1e293b; color: #64748b;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 700; border: 2px solid transparent;
  z-index: 2;
}

.step-indicator.active .circle {
  background: #6366f1; color: #fff;
  box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
}

.step-indicator.completed .circle {
  background: #10b981; color: #fff;
}

.label { font-size: 11px; font-weight: 600; color: #64748b; text-align: center; }
.step-indicator.active .label { color: #e2e8f0; }

.line {
  position: absolute;
  top: 14px;
  left: 50%;
  width: 100%;
  height: 2px;
  background: #1e293b;
  z-index: 1;
}
.step-indicator.completed .line { background: #10b981; }

.step-content {
  max-width: 600px;
  margin: 0 auto;
}

.step-intro { margin-bottom: 32px; text-align: center; }
.step-intro h4 { margin: 0 0 8px; font-size: 20px; color: #f8fafc; }
.step-intro p { margin: 0; color: #94a3b8; font-size: 14px; }

.fields-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}

.preview-field label {
  display: block; font-size: 13px; font-weight: 600; 
  color: #94a3b8; margin-bottom: 8px;
}
.required { color: #f87171; margin-left: 2px; }

.mock-input input, .mock-input textarea, .mock-input select {
  width: 100%; padding: 12px 16px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px; color: #94a3b8;
  font-size: 14px;
}

.check-mock {
  width: 20px; height: 20px; border-radius: 6px;
  border: 2px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
}

.preview-field .help-text {
  margin: 6px 0 0; font-size: 11px; color: #64748b;
}

.modal-footer {
  padding: 20px 32px;
  background: rgba(255, 255, 255, 0.02);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  align-items: center;
}

.flex-spacer { flex: 1; }

.btn-primary {
  background: #6366f1; color: #fff; border: none;
  padding: 10px 24px; border-radius: 12px; font-weight: 600;
  cursor: pointer;
}
.btn-secondary {
  background: rgba(255,255,255,0.05); color: #94a3b8; border: none;
  padding: 10px 24px; border-radius: 12px; font-weight: 600;
  cursor: pointer;
}
.btn-secondary:disabled { opacity: 0.3; cursor: not-allowed; }
</style>
