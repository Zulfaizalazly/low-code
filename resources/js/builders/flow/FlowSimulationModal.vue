<script setup>
/**
 * FlowSimulationModal — Two-phase modal: Preflight Check → Dry-Run Simulation.
 *
 * Phase 1 (preflight): Scans every node for config completeness, shows animated
 *   progress, and offers an inline fix form for missing fields.
 * Phase 2 (simulation): The original dry-run UI — JSON payload editor + execution
 *   path timeline.
 */
import { ref, watch, nextTick, computed } from 'vue'
import usePreflightChecker from './composables/usePreflightChecker'
import { getConfigFields } from './composables/useConfigFields'
import useFieldHintService from './composables/useFieldHintService'
import FieldHelperPopover from './FieldHelperPopover.vue'

const props = defineProps({
  show: Boolean,
  flowId: [String, Number],
  versionId: [String, Number],
  flowKey: String,
  nodes: { type: Array, default: () => [] },
  commands: { type: Array, default: () => [] },
})

const emit = defineEmits(['close', 'update-node-config'])

// ─── Phase management ───
const phase = ref('preflight') // 'preflight' | 'simulation'

// ─── Preflight composable ───
const {
  isScanning,
  scanProgress,
  currentNode,
  completedNodes,
  results,
  runScan,
  cancelScan,
} = usePreflightChecker()

// ─── AI Field Hint composable ───
const { popover: hintPopover, openHint, fetchDetailed, askFollowUp, retry: retryHint, closePopover: closeHintPopover, clearCache: clearHintCache } = useFieldHintService()

// ─── Inline fix form data ───
const fixFormData = ref({})

// ─── Simulation state (preserved from original) ───
const isSimulating = ref(false)
const simulationResult = ref(null)
const triggerDataJson = ref('{\n  "amount": 5000,\n  "customer_id": 1\n}')

// ─── Computed helpers ───
const scannableNodeCount = computed(() => {
  return props.nodes.filter(n => n.data?.nodeType).length
})

const progressPercent = computed(() => {
  if (scannableNodeCount.value === 0) return 0
  return Math.round((scanProgress.value / scannableNodeCount.value) * 100)
})

const failedNodes = computed(() => {
  if (!results.value) return []
  return results.value.nodeResults.filter(r => !r.passed)
})

// ─── Auto-trigger scan when modal opens ───
watch(() => props.show, async (newVal) => {
  if (newVal) {
    phase.value = 'preflight'
    fixFormData.value = {}
    simulationResult.value = null
    // Small delay to let the DOM render before starting scan
    await nextTick()
    await runScan(props.nodes, props.commands)
  } else {
    cancelScan()
    clearHintCache()
  }
}, { immediate: true })

// ─── Phase transitions ───
function proceedToSimulation() {
  phase.value = 'simulation'
}

function backToPreflight() {
  phase.value = 'preflight'
}

// ─── Inline fix form helpers ───
function getFixValue(nodeId, key) {
  return fixFormData.value[nodeId]?.[key] ?? ''
}

function setFixValue(nodeId, key, value) {
  if (!fixFormData.value[nodeId]) {
    fixFormData.value[nodeId] = {}
  }
  fixFormData.value[nodeId][key] = value
}

async function saveAndRecheck() {
  // Emit update-node-config for each filled field
  for (const [nodeId, fields] of Object.entries(fixFormData.value)) {
    for (const [key, value] of Object.entries(fields)) {
      if (value !== '' && value !== undefined && value !== null) {
        emit('update-node-config', { nodeId, key, value })
      }
    }
  }

  await nextTick()

  // Clear form data and re-run scan
  fixFormData.value = {}
  await runScan(props.nodes, props.commands)
}

// ─── Simulation (preserved from original) ───
async function runSimulation() {
  try {
    isSimulating.value = true
    simulationResult.value = null

    const response = await fetch(`/api/studio/flows/${props.flowId}/simulate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
      },
      body: JSON.stringify({
        trigger_data: JSON.parse(triggerDataJson.value)
      }),
    })

    const data = await response.json()
    simulationResult.value = data
  } catch (error) {
    alert('Simulation failed: ' + error.message)
  } finally {
    isSimulating.value = false
  }
}

// ─── Field type helpers for inline fix form ───
function isStringOptions(field) {
  return field.type === 'select' && Array.isArray(field.options) && field.options.length > 0 && typeof field.options[0] === 'string'
}

function isCommandSelect(field) {
  return field.type === 'select' && field.key === 'command_class'
}
</script>

<template>
  <div v-if="show" class="sim-overlay" @click.self="emit('close')">
    <div class="sim-modal liquid-glass">
      <header class="modal-header">
        <div class="header-title">
          <div class="icon-pulse">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
              <path v-if="phase === 'preflight'" d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
              <path v-else d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
          </div>
          <h3>{{ phase === 'preflight' ? 'Preflight Check' : 'Flow Simulation Engine' }}</h3>
          <button v-if="phase === 'simulation'" class="back-btn" @click="backToPreflight">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            Back to Preflight
          </button>
        </div>
        <button class="close-btn" @click="emit('close')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </header>

      <div class="modal-body custom-scroll">
        <!-- ═══════════════════════════════════════════════════ -->
        <!-- PHASE 1: PREFLIGHT CHECK                           -->
        <!-- ═══════════════════════════════════════════════════ -->
        <transition name="phase-fade" mode="out-in">
          <div v-if="phase === 'preflight'" key="preflight" class="body-grid">
            <!-- LEFT SIDE: Scan Progress -->
            <section class="config-side">
              <div class="section-label">
                <span class="dot" :class="{ highlight: isScanning }"></span>
                PREFLIGHT CHECK
              </div>

              <!-- Progress bar -->
              <div class="progress-container">
                <div class="progress-bar-track">
                  <div class="progress-bar-fill" :style="{ width: progressPercent + '%' }"></div>
                </div>
                <div class="progress-text">{{ progressPercent }}%</div>
              </div>

              <!-- Current node being scanned -->
              <div v-if="isScanning && currentNode" class="current-scan-node">
                <div class="scan-pulse"></div>
                <span class="scan-label">{{ currentNode.label }}</span>
                <span class="scan-type-badge">{{ currentNode.nodeType }}</span>
              </div>

              <!-- Initializing state -->
              <div v-else-if="!results && !isScanning" class="current-scan-node">
                <div class="scan-pulse"></div>
                <span class="scan-label">Preparing scan...</span>
              </div>

              <!-- Completed nodes list -->
              <div class="completed-nodes-list custom-scroll">
                <div
                  v-for="node in completedNodes"
                  :key="node.nodeId"
                  class="completed-node-item"
                >
                  <span class="node-status-dot" :class="node.passed ? 'pass' : 'fail'"></span>
                  <span class="node-item-label">{{ node.label }}</span>
                  <span v-if="!node.passed" class="missing-count">{{ node.missingFields.length }} missing</span>
                </div>
              </div>
            </section>

            <!-- RIGHT SIDE: Readiness Report -->
            <section class="result-side">
              <div class="section-label">
                <span class="dot" :class="{ highlight: !!results }"></span>
                READINESS REPORT
              </div>

              <!-- Scanning shimmer -->
              <div v-if="isScanning || (!results && !isScanning)" class="shimmer-path">
                <div class="shimmer-status-text" v-if="!isScanning && !results">Initializing scan...</div>
                <div v-for="i in 3" :key="i" class="shimmer-item"></div>
              </div>

              <!-- ALL PASS state -->
              <div v-else-if="results && results.totalFailed === 0" class="all-pass-state">
                <div class="success-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                  </svg>
                </div>
                <h4 class="success-title">All {{ results.totalScanned }} nodes ready</h4>
                <p class="success-subtitle">Every node has complete configuration. You're good to go.</p>
                <button class="run-btn-gradient" @click="proceedToSimulation">
                  <span class="btn-content">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                      <polygon points="5 3 19 12 5 21 5 3"></polygon>
                    </svg>
                    Proceed to Simulation
                  </span>
                </button>
              </div>

              <!-- HAS FAILURES state -->
              <div v-else-if="results && results.totalFailed > 0" class="failures-state">
                <div class="failures-summary">
                  <span class="failures-count">{{ results.totalFailed }}</span>
                  <span class="failures-text">of {{ results.totalScanned }} nodes need attention</span>
                </div>

                <!-- Inline fix form -->
                <div class="fix-form custom-scroll">
                  <div v-for="node in failedNodes" :key="node.nodeId" class="fix-node-section">
                    <div class="fix-node-header">
                      <span class="fix-node-label">{{ node.label }}</span>
                      <span class="fix-node-type-badge">{{ node.nodeType }}</span>
                    </div>

                    <div v-for="field in node.missingFields" :key="field.key" class="fix-field-group">
                      <div class="fix-field-label-row">
                        <label class="fix-field-label">{{ field.label }}</label>
                        <button
                          class="hint-icon-btn"
                          @click.stop="openHint($event.currentTarget, node.nodeType, field.key, field.label)"
                          title="Ask AI"
                        >
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                          </svg>
                          <span class="hint-icon-label">Ask AI</span>
                        </button>
                      </div>

                      <!-- Select with string options -->
                      <select
                        v-if="isStringOptions(field)"
                        :value="getFixValue(node.nodeId, field.key)"
                        class="fix-field-input fix-select"
                        @change="(e) => setFixValue(node.nodeId, field.key, e.target.value)"
                      >
                        <option value="">Select...</option>
                        <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                      </select>

                      <!-- Select with command objects -->
                      <select
                        v-else-if="isCommandSelect(field)"
                        :value="getFixValue(node.nodeId, field.key)"
                        class="fix-field-input fix-select"
                        @change="(e) => setFixValue(node.nodeId, field.key, e.target.value)"
                      >
                        <option value="">Select command...</option>
                        <option v-for="cmd in commands" :key="cmd.class" :value="cmd.class">
                          {{ cmd.name }} ({{ cmd.domain }})
                        </option>
                      </select>

                      <!-- Textarea -->
                      <textarea
                        v-else-if="field.type === 'textarea'"
                        :value="getFixValue(node.nodeId, field.key)"
                        class="fix-field-input fix-textarea"
                        rows="3"
                        :placeholder="field.placeholder || ''"
                        @input="(e) => setFixValue(node.nodeId, field.key, e.target.value)"
                      ></textarea>

                      <!-- JSON -->
                      <textarea
                        v-else-if="field.type === 'json'"
                        :value="getFixValue(node.nodeId, field.key)"
                        class="fix-field-input fix-textarea fix-json"
                        rows="3"
                        :placeholder="field.placeholder || '{}'"
                        @input="(e) => setFixValue(node.nodeId, field.key, e.target.value)"
                      ></textarea>

                      <!-- Number -->
                      <input
                        v-else-if="field.type === 'number'"
                        :value="getFixValue(node.nodeId, field.key)"
                        type="number"
                        class="fix-field-input"
                        @input="(e) => setFixValue(node.nodeId, field.key, parseFloat(e.target.value))"
                      />

                      <!-- Text (default) -->
                      <input
                        v-else
                        :value="getFixValue(node.nodeId, field.key)"
                        type="text"
                        class="fix-field-input"
                        :placeholder="field.placeholder || ''"
                        @input="(e) => setFixValue(node.nodeId, field.key, e.target.value)"
                      />
                    </div>
                  </div>
                </div>

                <button class="run-btn-gradient recheck-btn" @click="saveAndRecheck">
                  <span class="btn-content">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                      <polyline points="23 4 23 10 17 10"></polyline>
                      <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    Save &amp; Re-check
                  </span>
                </button>
              </div>
            </section>
          </div>

          <!-- ═══════════════════════════════════════════════════ -->
          <!-- PHASE 2: SIMULATION (preserved from original)      -->
          <!-- ═══════════════════════════════════════════════════ -->
          <div v-else key="simulation" class="body-grid">
            <!-- Configuration -->
            <section class="config-side">
              <div class="section-label">
                <span class="dot"></span>
                Input Payload
              </div>
              <div class="editor-container">
                <div class="editor-header">
                  <span class="lang-label">JSON</span>
                  <span class="file-icon">{}</span>
                </div>
                <textarea
                  v-model="triggerDataJson"
                  rows="10"
                  class="payload-editor"
                  placeholder='{ "key": "value" }'
                ></textarea>
              </div>

              <button @click="runSimulation" :disabled="isSimulating" class="run-btn-gradient">
                <span v-if="isSimulating" class="loader"></span>
                <span v-else class="btn-content">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                  </svg>
                  Launch Simulation
                </span>
              </button>
            </section>

            <!-- Execution Path -->
            <section class="result-side">
              <div class="section-label">
                <span class="dot highlight"></span>
                Execution Path
              </div>

              <div v-if="!simulationResult && !isSimulating" class="empty-path">
                <div class="empty-icon">🛸</div>
                <p>Ready to simulate logic... Launch to see path analysis.</p>
              </div>

              <div v-if="isSimulating" class="shimmer-path">
                <div v-for="i in 3" :key="i" class="shimmer-item"></div>
              </div>

              <div v-if="simulationResult" class="path-timeline">
                <div v-if="!simulationResult.success" class="error-card">
                  <div class="error-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    Simulation Failed
                  </div>
                  <p>{{ simulationResult.message }}</p>
                </div>

                <div
                  v-for="(nodeKey, idx) in simulationResult.execution_path"
                  :key="idx"
                  class="timeline-item completed"
                >
                  <div class="item-line"></div>
                  <div class="item-indicator">
                    <div class="inner-dot"></div>
                  </div>
                  <div class="item-card">
                    <div class="item-header">
                      <span class="node-key">{{ nodeKey }}</span>
                      <span class="status-tag completed">PROCESSED</span>
                    </div>
                    <div class="item-output" v-if="simulationResult.node_outputs && simulationResult.node_outputs[nodeKey]">
                      <div class="output-label">OUTPUT DATA</div>
                      <pre>{{ JSON.stringify(simulationResult.node_outputs[nodeKey], null, 2) }}</pre>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </transition>
      </div>

      <FieldHelperPopover
        :popover="hintPopover"
        @fetch-detailed="fetchDetailed"
        @ask-follow-up="askFollowUp"
        @retry="retryHint"
        @close="closeHintPopover"
      />
    </div>
  </div>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════════════
   OVERLAY & MODAL SHELL
   ═══════════════════════════════════════════════════════════════ */
.sim-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(12px) saturate(180%);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.liquid-glass {
  background: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.sim-modal {
  width: 100%;
  max-width: 1000px;
  max-height: 85vh;
  border-radius: 28px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  color: #f1f5f9;
}

/* ═══════════════════════════════════════════════════════════════
   HEADER
   ═══════════════════════════════════════════════════════════════ */
.modal-header {
  padding: 24px 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.header-title {
  display: flex;
  align-items: center;
  gap: 16px;
}

.icon-pulse {
  width: 40px;
  height: 40px;
  background: rgba(99, 102, 241, 0.15);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #818cf8;
  box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
}

.header-title h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  letter-spacing: -0.02em;
  background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #94a3b8;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #f1f5f9;
}

.close-btn {
  background: rgba(255, 255, 255, 0.05);
  border: none;
  color: #94a3b8;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
}

/* ═══════════════════════════════════════════════════════════════
   BODY & GRID
   ═══════════════════════════════════════════════════════════════ */
.modal-body {
  padding: 0;
  overflow-y: auto;
  flex: 1;
}

.body-grid {
  display: grid;
  grid-template-columns: 380px 1fr;
  min-height: 500px;
}

.config-side {
  padding: 32px;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
  background: rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
}

.result-side {
  padding: 32px;
  background: rgba(15, 23, 42, 0.2);
  display: flex;
  flex-direction: column;
}

.section-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.05em;
  margin-bottom: 20px;
}

.section-label .dot {
  width: 6px;
  height: 6px;
  background: #475569;
  border-radius: 50%;
}

.section-label .dot.highlight {
  background: #818cf8;
  box-shadow: 0 0 8px #818cf8;
}

/* ═══════════════════════════════════════════════════════════════
   PHASE TRANSITION
   ═══════════════════════════════════════════════════════════════ */
.phase-fade-enter-active,
.phase-fade-leave-active {
  transition: opacity 0.25s ease;
}

.phase-fade-enter-from,
.phase-fade-leave-to {
  opacity: 0;
}

/* ═══════════════════════════════════════════════════════════════
   PREFLIGHT — Progress Bar
   ═══════════════════════════════════════════════════════════════ */
.progress-container {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.progress-bar-track {
  flex: 1;
  height: 6px;
  background: rgba(255, 255, 255, 0.06);
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #6366f1, #4f46e5);
  border-radius: 3px;
  transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.progress-text {
  font-size: 12px;
  font-weight: 700;
  color: #818cf8;
  min-width: 36px;
  text-align: right;
}

/* ═══════════════════════════════════════════════════════════════
   PREFLIGHT — Current Scan Node
   ═══════════════════════════════════════════════════════════════ */
.current-scan-node {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  background: rgba(99, 102, 241, 0.08);
  border: 1px solid rgba(99, 102, 241, 0.15);
  border-radius: 12px;
  margin-bottom: 20px;
}

.scan-pulse {
  width: 8px;
  height: 8px;
  background: #818cf8;
  border-radius: 50%;
  animation: pulse-glow 1.2s ease-in-out infinite;
}

@keyframes pulse-glow {
  0%, 100% { opacity: 1; box-shadow: 0 0 4px #818cf8; }
  50% { opacity: 0.4; box-shadow: 0 0 12px #818cf8; }
}

.scan-label {
  font-size: 13px;
  font-weight: 600;
  color: #e2e8f0;
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.scan-type-badge {
  font-size: 10px;
  padding: 3px 8px;
  background: rgba(99, 102, 241, 0.15);
  color: #a5b4fc;
  border-radius: 8px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* ═══════════════════════════════════════════════════════════════
   PREFLIGHT — Completed Nodes List
   ═══════════════════════════════════════════════════════════════ */
.completed-nodes-list {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.completed-node-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.02);
  transition: background 0.15s ease;
}

.completed-node-item:hover {
  background: rgba(255, 255, 255, 0.04);
}

.node-status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.node-status-dot.pass {
  background: #10b981;
  box-shadow: 0 0 6px rgba(16, 185, 129, 0.4);
}

.node-status-dot.fail {
  background: #ef4444;
  box-shadow: 0 0 6px rgba(239, 68, 68, 0.4);
}

.node-item-label {
  font-size: 12px;
  color: #cbd5e1;
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.missing-count {
  font-size: 10px;
  color: #f87171;
  font-weight: 600;
  white-space: nowrap;
}

/* ═══════════════════════════════════════════════════════════════
   PREFLIGHT — All Pass State
   ═══════════════════════════════════════════════════════════════ */
.all-pass-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  text-align: center;
  padding: 40px 20px;
}

.success-icon {
  color: #10b981;
  margin-bottom: 20px;
  animation: success-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes success-pop {
  0% { transform: scale(0); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

.success-title {
  margin: 0 0 8px;
  font-size: 20px;
  font-weight: 700;
  color: #f1f5f9;
}

.success-subtitle {
  margin: 0 0 28px;
  font-size: 13px;
  color: #64748b;
  max-width: 260px;
}

/* ═══════════════════════════════════════════════════════════════
   PREFLIGHT — Failures State
   ═══════════════════════════════════════════════════════════════ */
.failures-state {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.failures-summary {
  display: flex;
  align-items: baseline;
  gap: 8px;
  margin-bottom: 20px;
}

.failures-count {
  font-size: 28px;
  font-weight: 800;
  color: #f87171;
  line-height: 1;
}

.failures-text {
  font-size: 13px;
  color: #94a3b8;
}

/* ═══════════════════════════════════════════════════════════════
   INLINE FIX FORM
   ═══════════════════════════════════════════════════════════════ */
.fix-form {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 16px;
}

.fix-node-section {
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.fix-node-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.fix-node-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 14px;
}

.fix-node-label {
  font-size: 14px;
  font-weight: 700;
  color: #e2e8f0;
}

.fix-node-type-badge {
  font-size: 10px;
  padding: 3px 8px;
  background: rgba(99, 102, 241, 0.12);
  color: #a5b4fc;
  border-radius: 8px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.fix-field-group {
  margin-bottom: 14px;
}

.fix-field-label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.fix-field-label-row {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 6px;
}

.fix-field-label-row .fix-field-label {
  margin-bottom: 0;
}

.hint-icon-btn {
  height: 20px;
  min-width: 20px;
  padding: 0 8px;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.hint-icon-label {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.3px;
}

.hint-icon-btn:hover {
  color: #a5b4fc;
  border-color: rgba(99, 102, 241, 0.4);
  background: rgba(99, 102, 241, 0.1);
}

.fix-field-input {
  width: 100%;
  padding: 10px 12px;
  background: #020617;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  color: #94a3b8;
  font-size: 13px;
  outline: none;
  transition: all 0.2s ease;
  box-sizing: border-box;
  font-family: inherit;
}

.fix-field-input:focus {
  border-color: rgba(99, 102, 241, 0.4);
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
  color: #e2e8f0;
}

.fix-field-input::placeholder {
  color: #334155;
}

.fix-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 32px;
  cursor: pointer;
}

.fix-select option {
  background: #0f172a;
  color: #94a3b8;
}

.fix-textarea {
  resize: vertical;
  min-height: 60px;
  font-family: inherit;
}

.fix-json {
  font-family: 'SF Mono', 'Fira Code', 'JetBrains Mono', Consolas, monospace;
  font-size: 12px;
  line-height: 1.5;
}

.recheck-btn {
  flex-shrink: 0;
}

/* ═══════════════════════════════════════════════════════════════
   SIMULATION PHASE — Editor & Buttons (preserved)
   ═══════════════════════════════════════════════════════════════ */
.editor-container {
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: #020617;
}

.editor-header {
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.03);
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.lang-label {
  font-size: 10px;
  font-weight: 700;
  color: #475569;
}

.file-icon {
  font-family: monospace;
  color: #818cf8;
  font-size: 12px;
}

.payload-editor {
  width: 100%;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-family: 'SF Mono', 'Fira Code', monospace;
  padding: 16px;
  font-size: 13px;
  resize: vertical;
  line-height: 1.6;
  box-sizing: border-box;
}

.payload-editor:focus {
  outline: none;
}

.run-btn-gradient {
  width: 100%;
  margin-top: 24px;
  padding: 14px;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  border: none;
  border-radius: 14px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
  font-size: 14px;
}

.run-btn-gradient:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
  filter: brightness(1.1);
}

.run-btn-gradient:active {
  transform: translateY(0);
}

.run-btn-gradient:disabled {
  opacity: 0.6;
  cursor: wait;
}

.btn-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

/* ═══════════════════════════════════════════════════════════════
   SIMULATION PHASE — Timeline (preserved)
   ═══════════════════════════════════════════════════════════════ */
.path-timeline {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.timeline-item {
  position: relative;
  padding-left: 32px;
  padding-bottom: 32px;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.item-line {
  position: absolute;
  left: 7px;
  top: 20px;
  bottom: 0;
  width: 2px;
  background: rgba(255, 255, 255, 0.05);
}

.timeline-item:last-child .item-line {
  display: none;
}

.item-indicator {
  position: absolute;
  left: 0;
  top: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #1e293b;
  border: 2px solid #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}

.inner-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: transparent;
}

.timeline-item.completed .item-indicator {
  border-color: #10b981;
  box-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
}

.timeline-item.completed .inner-dot {
  background: #10b981;
}

.timeline-item.failed .item-indicator {
  border-color: #ef4444;
  box-shadow: 0 0 10px rgba(239, 68, 68, 0.3);
}

.timeline-item.failed .inner-dot {
  background: #ef4444;
}

.item-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 16px;
  transition: all 0.2s ease;
}

.item-card:hover {
  background: rgba(255, 255, 255, 0.05);
  transform: translateX(4px);
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.node-key {
  font-size: 13px;
  font-weight: 700;
  color: #f1f5f9;
}

.status-tag {
  font-size: 9px;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 6px;
  letter-spacing: 0.05em;
}

.status-tag.completed {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.status-tag.failed {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.item-output {
  margin-top: 12px;
  background: rgba(0, 0, 0, 0.2);
  padding: 12px;
  border-radius: 10px;
  font-size: 11px;
  color: #94a3b8;
  border: 1px solid rgba(255, 255, 255, 0.03);
}

.output-label {
  font-size: 9px;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
}

.item-output pre {
  margin: 0;
  white-space: pre-wrap;
  font-family: inherit;
}

/* ═══════════════════════════════════════════════════════════════
   EMPTY STATE & ERROR (preserved)
   ═══════════════════════════════════════════════════════════════ */
.empty-path {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 300px;
  color: #475569;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.3;
}

.empty-path p {
  font-size: 13px;
  max-width: 200px;
  text-align: center;
}

.error-card {
  background: rgba(239, 68, 68, 0.05);
  border: 1px solid rgba(239, 68, 68, 0.1);
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 24px;
}

.error-header {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #fca5a5;
  font-weight: 700;
  font-size: 14px;
  margin-bottom: 8px;
}

.error-card p {
  margin: 0;
  font-size: 13px;
  color: #f87171;
  opacity: 0.8;
}

/* ═══════════════════════════════════════════════════════════════
   SHIMMER & LOADER (preserved)
   ═══════════════════════════════════════════════════════════════ */
.shimmer-path {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.shimmer-status-text {
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
  margin-bottom: 4px;
}

.shimmer-item {
  height: 80px;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 16px;
  position: relative;
  overflow: hidden;
}

.shimmer-item::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.loader {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto;
  display: block;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ═══════════════════════════════════════════════════════════════
   CUSTOM SCROLLBAR (preserved)
   ═══════════════════════════════════════════════════════════════ */
.custom-scroll::-webkit-scrollbar {
  width: 6px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>
