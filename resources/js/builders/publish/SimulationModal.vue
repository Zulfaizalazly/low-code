<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  show: Boolean,
  versionId: [String, Number],
  flowKey: String,
})

const emit = defineEmits(['close'])

const testData = ref({})
const isRunning = ref(false)
const simulationResult = ref(null)
const activeTab = ref('input')
const expandedNodes = ref({})

// Sample field definitions based on common flow inputs
const inputFields = ref([
  { key: 'ic_no', label: 'Customer IC', type: 'text', placeholder: '900101-14-5567' },
  { key: 'full_name', label: 'Full Name', type: 'text', placeholder: 'MUHAMMAD ALI' },
  { key: 'weight', label: 'Gold Weight (g)', type: 'number', placeholder: '10.50' },
  { key: 'purity', label: 'Purity Factor', type: 'number', placeholder: '0.916' },
  { key: 'branch_code', label: 'Branch Code', type: 'text', placeholder: 'HQ-01' },
])

const statusColor = computed(() => {
  if (!simulationResult.value) return '#86868b'
  return simulationResult.value.status === 'success' ? '#34c759' : '#ff3b30'
})

const icons = {
  test: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.77 3.77z"></path></svg>',
  results: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',
  history: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
  check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
  x: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
  clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
  zap: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
  chevronRight: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>',
  chevronDown: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>'
}


const totalDuration = computed(() => {
  if (!simulationResult.value?.results) return 0
  return simulationResult.value.results.reduce((sum, r) => sum + (r.duration_ms || 0), 0)
})

async function runSimulation() {
  if (Object.keys(testData.value).length === 0) {
    alert('Please provide at least one test input.')
    return
  }

  isRunning.value = true
  simulationResult.value = null
  
  try {
    const res = await fetch(`/api/studio/versions/${props.versionId}/simulate/${props.flowKey}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ input_data: testData.value })
    })
    
    const data = await res.json()
    
    if (data.success) {
      simulationResult.value = data.log
      activeTab.value = 'results'
    } else {
      alert('Simulation failed: ' + data.message)
    }
  } catch (error) {
    alert('Simulation error: ' + error.message)
  } finally {
    isRunning.value = false
  }
}

function toggleNode(nodeKey) {
  expandedNodes.value[nodeKey] = !expandedNodes.value[nodeKey]
}

function formatJSON(obj) {
  return JSON.stringify(obj, null, 2)
}

function getNodeIcon(status) {
  if (status === 'completed') return '✅'
  if (status === 'failed') return '❌'
  return '⏳'
}

function clearForm() {
  testData.value = {}
  simulationResult.value = null
  activeTab.value = 'input'
}
</script>

<template>
  <div v-if="show" class="simulation-overlay">
    <div class="simulation-modal liquid-glass">
      <div class="simulation-header">
        <div class="header-main">
          <div class="header-icon-box" v-html="icons.test"></div>
          <div>
            <h3>Flow Simulation</h3>
            <span class="flow-badge">{{ flowKey }}</span>
          </div>
        </div>
        <button @click="$emit('close')" class="close-btn">
          <span v-html="icons.x"></span>
        </button>
      </div>

      <div class="simulation-tabs">
        <button 
          :class="['tab-btn', { active: activeTab === 'input' }]"
          @click="activeTab = 'input'"
        >
          <span class="tab-icon" v-html="icons.test"></span>
          Test Data
        </button>
        <button 
          :class="['tab-btn', { active: activeTab === 'results' }]"
          @click="activeTab = 'results'"
          :disabled="!simulationResult"
        >
          <span class="tab-icon" v-html="icons.results"></span>
          Results
        </button>
        <button 
          :class="['tab-btn', { active: activeTab === 'history' }]"
          @click="activeTab = 'history'"
        >
          <span class="tab-icon" v-html="icons.history"></span>
          History
        </button>
      </div>

      <div class="simulation-body">
        <!-- Input Tab -->
        <div v-if="activeTab === 'input'" class="input-section">
          <div class="input-description">
            <p>Provide test data to simulate this flow. The engine will execute logic paths and variable transformations without committing real database side-effects.</p>
          </div>

          <div class="input-form">
            <div v-for="field in inputFields" :key="field.key" class="form-field">
              <label>{{ field.label }}</label>
              <input 
                v-model="testData[field.key]"
                :type="field.type"
                :placeholder="field.placeholder"
                class="field-input"
              />
            </div>

            <div class="custom-json-section">
              <label>Additional JSON Context</label>
              <textarea 
                v-model="testData._custom"
                placeholder='{"custom_field": "value"}'
                class="json-input"
              ></textarea>
              <small>Raw JSON object to be merged into flow execution context.</small>
            </div>
          </div>
        </div>

        <!-- Results Tab -->
        <div v-if="activeTab === 'results'" class="results-section">
          <div v-if="!simulationResult" class="empty-state">
            <div class="empty-icon" v-html="icons.results"></div>
            <p>No simulation results yet. Run a simulation to see execution traces.</p>
          </div>

          <div v-else class="results-content">
            <!-- Summary -->
            <div class="results-summary">
              <div class="summary-card">
                <span class="summary-label">Execution Status</span>
                <span class="summary-value" :style="{ color: statusColor }">
                  {{ simulationResult.status.toUpperCase() }}
                </span>
              </div>
              <div class="summary-card">
                <span class="summary-label">Nodes Traced</span>
                <span class="summary-value">{{ simulationResult.results?.length || 0 }}</span>
              </div>
              <div class="summary-card">
                <span class="summary-label">Latency</span>
                <span class="summary-value">{{ totalDuration }}ms</span>
              </div>
              <div class="summary-card">
                <span class="summary-label">Timestamp</span>
                <span class="summary-value">{{ new Date(simulationResult.executed_at).toLocaleTimeString() }}</span>
              </div>
            </div>

            <!-- Node Results -->
            <div class="node-results">
              <h4>Execution Trace</h4>
              <div 
                v-for="(node, idx) in simulationResult.results" 
                :key="idx"
                :class="['node-result-card', node.status]"
              >
                <div class="node-header" @click="toggleNode(node.node_key)">
                  <div class="node-info">
                    <span class="node-icon" v-html="node.status === 'completed' ? icons.check : icons.x" :style="{ color: node.status === 'completed' ? '#34c759' : '#ff3b30' }"></span>
                    <span class="node-key">{{ node.node_key.replace(/_/g, ' ').toUpperCase() }}</span>
                    <span class="node-status-tag">{{ node.status }}</span>
                  </div>
                  <div class="node-meta">
                    <span class="node-duration">{{ node.duration_ms }}ms</span>
                    <span class="expand-icon" v-html="expandedNodes[node.node_key] ? icons.chevronDown : icons.chevronRight"></span>
                  </div>
                </div>

                <div v-if="expandedNodes[node.node_key]" class="node-details">
                  <div class="detail-grid">
                    <div class="detail-section">
                      <h5>Input State</h5>
                      <pre class="json-display">{{ formatJSON(node.input) }}</pre>
                    </div>

                    <div class="detail-section">
                      <h5>Output State</h5>
                      <pre class="json-display">{{ formatJSON(node.output) }}</pre>
                    </div>
                  </div>

                  <div v-if="node.error" class="detail-section error">
                    <h5>Exception Trace</h5>
                    <pre class="error-display">{{ node.error }}</pre>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- History Tab -->
        <div v-if="activeTab === 'history'" class="history-section">
          <div class="history-placeholder">
            <div class="empty-icon" v-html="icons.history"></div>
            <p>Simulation history is currently being synchronized.</p>
            <small>Soon you'll be able to compare results across different version revisions.</small>
          </div>
        </div>
      </div>

      <div class="simulation-footer">
        <div class="footer-info">
          <div v-if="isRunning" class="running-indicator">
            <span class="spin-icon" v-html="icons.clock"></span>
            Simulating logic paths...
          </div>
          <div v-else-if="simulationResult" class="result-indicator">
            <span class="check-icon" v-html="icons.check"></span>
            Trace completed successfully
          </div>
        </div>
        <div class="footer-actions">
          <button @click="clearForm" class="btn-secondary" :disabled="isRunning">Reset</button>
          <button @click="$emit('close')" class="btn-secondary">Close</button>
          <button 
            @click="runSimulation" 
            class="btn-primary-gradient"
            :disabled="isRunning"
          >
            <span v-if="isRunning">Simulating...</span>
            <span v-else>Run Simulation</span>
          </button>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped>
.simulation-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  z-index: 120;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.simulation-modal {
  width: 100%;
  max-width: 960px;
  max-height: 85vh;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(30px);
  -webkit-backdrop-filter: blur(30px);
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 28px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.simulation-header {
  padding: 24px 32px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-main {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon-box {
  width: 44px;
  height: 44px;
  background: #007aff;
  color: white;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.header-icon-box svg {
  width: 24px;
  height: 24px;
}

.simulation-header h3 {
  margin: 0 0 2px 0;
  font-size: 19px;
  color: #1d1d1f;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.flow-badge {
  font-size: 11px;
  background: #f5f5f7;
  padding: 3px 10px;
  border-radius: 6px;
  color: #86868b;
  border: 1px solid rgba(0, 0, 0, 0.05);
  font-family: 'SF Mono', monospace;
  font-weight: 600;
}

.close-btn {
  background: transparent;
  border: none;
  color: #86868b;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.close-btn svg {
  width: 16px;
  height: 16px;
}

.close-btn:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #1d1d1f;
}

.simulation-tabs {
  display: flex;
  gap: 8px;
  padding: 12px 32px;
  background: rgba(0, 0, 0, 0.02);
}

.tab-btn {
  background: transparent;
  border: 1px solid transparent;
  color: #86868b;
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.tab-icon {
  width: 16px;
  height: 16px;
}

.tab-btn:hover:not(:disabled) {
  background: rgba(0, 0, 0, 0.04);
  color: #1d1d1f;
}

.tab-btn.active {
  background: white;
  border-color: rgba(0,0,0,0.05);
  color: #007aff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.tab-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.simulation-body {
  flex: 1;
  overflow-y: auto;
  padding: 32px;
}

/* Input Section */
.input-description {
  margin-bottom: 32px;
  padding: 18px;
  background: #f0f7ff;
  border: 1px solid #c7e3ff;
  border-radius: 16px;
}

.input-description p {
  margin: 0;
  color: #004a99;
  font-size: 14px;
  line-height: 1.5;
  font-weight: 500;
}

.input-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-field label {
  font-size: 12px;
  font-weight: 700;
  color: #86868b;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.field-input {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  padding: 12px 16px;
  color: #1d1d1f;
  font-size: 14px;
  outline: none;
  transition: all 0.2s;
  box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}

.field-input:focus {
  border-color: #007aff;
  box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
}

.custom-json-section {
  grid-column: span 2;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.custom-json-section label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: #86868b;
  margin-bottom: 10px;
  text-transform: uppercase;
}

.json-input {
  width: 100%;
  background: #f5f5f7;
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 14px;
  padding: 16px;
  color: #1d1d1f;
  font-size: 13px;
  font-family: 'SF Mono', monospace;
  resize: vertical;
  min-height: 100px;
  outline: none;
}

.custom-json-section small {
  display: block;
  margin-top: 8px;
  color: #86868b;
  font-size: 12px;
}

/* Results Section */
.results-summary {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 40px;
}

.summary-card {
  background: #f5f5f7;
  padding: 20px;
  border-radius: 18px;
  border: 1px solid rgba(0, 0, 0, 0.02);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.summary-label {
  font-size: 10px;
  color: #86868b;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.05em;
}

.summary-value {
  font-size: 22px;
  font-weight: 700;
  color: #1d1d1f;
}

.node-results h4 {
  margin: 0 0 20px 0;
  font-size: 14px;
  color: #1d1d1f;
  font-weight: 700;
}

.node-result-card {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 16px;
  margin-bottom: 12px;
  overflow: hidden;
  transition: all 0.2s;
}

.node-result-card:hover {
  border-color: rgba(0, 0, 0, 0.1);
  box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}

.node-header {
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
}

.node-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.node-icon {
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.node-key {
  font-family: 'SF Mono', monospace;
  font-size: 13px;
  color: #1d1d1f;
  font-weight: 600;
}

.node-status-tag {
  font-size: 10px;
  padding: 3px 8px;
  border-radius: 6px;
  background: #f5f5f7;
  color: #86868b;
  text-transform: uppercase;
  font-weight: 700;
}

.node-meta {
  display: flex;
  align-items: center;
  gap: 16px;
}

.node-duration {
  font-size: 12px;
  color: #86868b;
  font-family: 'SF Mono', monospace;
}

.expand-icon {
  color: #c7c7cc;
  width: 14px;
  height: 14px;
}

.node-details {
  padding: 20px;
  background: #fafafa;
  border-top: 1px solid rgba(0, 0, 0, 0.03);
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.detail-section h5 {
  margin: 0 0 10px 0;
  font-size: 10px;
  color: #86868b;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.05em;
}

.json-display {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 12px;
  padding: 16px;
  color: #3a3a3c;
  font-size: 12px;
  font-family: 'SF Mono', monospace;
  overflow-x: auto;
  margin: 0;
  max-height: 300px;
}

.error-display {
  background: #fff5f5;
  border: 1px solid #feb2b2;
  border-radius: 12px;
  padding: 16px;
  color: #c53030;
  font-size: 12px;
  font-family: 'SF Mono', monospace;
  margin: 16px 0 0 0;
}

/* History Section */
.history-placeholder {
  text-align: center;
  padding: 80px 20px;
  color: #86868b;
}

.empty-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 20px;
  color: #d1d1d6;
}

.history-placeholder p {
  margin: 0 0 8px 0;
  font-size: 16px;
  font-weight: 600;
  color: #1d1d1f;
}

.history-placeholder small {
  font-size: 14px;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 20px;
}

/* Footer */
.simulation-footer {
  padding: 20px 32px;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
}

.footer-info {
  font-size: 13px;
}

.running-indicator {
  color: #007aff;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.spin-icon {
  animation: spin 2s linear infinite;
  width: 16px;
  height: 16px;
}

.result-indicator {
  color: #34c759;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.check-icon {
  width: 16px;
  height: 16px;
}

.footer-actions {
  display: flex;
  gap: 12px;
}

.btn-secondary {
  background: #f5f5f7;
  border: 1px solid rgba(0,0,0,0.05);
  color: #1d1d1f;
  padding: 10px 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover:not(:disabled) {
  background: #e5e5ea;
}

.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary-gradient {
  background: #007aff;
  border: none;
  color: white;
  padding: 10px 24px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(0, 122, 255, 0.2);
}

.btn-primary-gradient:hover:not(:disabled) {
  transform: translateY(-1px);
  background: #0071e3;
  box-shadow: 0 8px 20px rgba(0, 122, 255, 0.3);
}

.btn-primary-gradient:disabled {
  background: #d1d1d6;
  box-shadow: none;
  cursor: not-allowed;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
