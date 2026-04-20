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
  { key: 'customer_ic', label: 'Customer IC', type: 'text', placeholder: '900101011234' },
  { key: 'gold_weight', label: 'Gold Weight (g)', type: 'number', placeholder: '50.5' },
  { key: 'gold_purity', label: 'Gold Purity', type: 'number', placeholder: '916' },
  { key: 'loan_amount', label: 'Loan Amount (RM)', type: 'number', placeholder: '5000' },
  { key: 'branch_id', label: 'Branch ID', type: 'number', placeholder: '1' },
])

const statusColor = computed(() => {
  if (!simulationResult.value) return '#64748b'
  return simulationResult.value.status === 'success' ? '#10b981' : '#ef4444'
})

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
          <h3>🧪 Flow Simulation</h3>
          <span class="flow-badge">{{ flowKey }}</span>
        </div>
        <button @click="$emit('close')" class="close-btn">&times;</button>
      </div>

      <div class="simulation-tabs">
        <button 
          :class="['tab-btn', { active: activeTab === 'input' }]"
          @click="activeTab = 'input'"
        >
          📝 Test Data
        </button>
        <button 
          :class="['tab-btn', { active: activeTab === 'results' }]"
          @click="activeTab = 'results'"
          :disabled="!simulationResult"
        >
          📊 Results
        </button>
        <button 
          :class="['tab-btn', { active: activeTab === 'history' }]"
          @click="activeTab = 'history'"
        >
          📜 History
        </button>
      </div>

      <div class="simulation-body">
        <!-- Input Tab -->
        <div v-if="activeTab === 'input'" class="input-section">
          <div class="input-description">
            <p>Provide test data to simulate this flow. The flow will execute in simulation mode (no real side-effects).</p>
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
              <label>Additional JSON Data (Optional)</label>
              <textarea 
                v-model="testData._custom"
                placeholder='{"custom_field": "value"}'
                class="json-input"
              ></textarea>
              <small>Add any additional fields as JSON</small>
            </div>
          </div>
        </div>

        <!-- Results Tab -->
        <div v-if="activeTab === 'results'" class="results-section">
          <div v-if="!simulationResult" class="empty-state">
            <p>No simulation results yet. Run a simulation first.</p>
          </div>

          <div v-else class="results-content">
            <!-- Summary -->
            <div class="results-summary">
              <div class="summary-card">
                <span class="summary-label">Status</span>
                <span class="summary-value" :style="{ color: statusColor }">
                  {{ simulationResult.status.toUpperCase() }}
                </span>
              </div>
              <div class="summary-card">
                <span class="summary-label">Nodes Executed</span>
                <span class="summary-value">{{ simulationResult.results?.length || 0 }}</span>
              </div>
              <div class="summary-card">
                <span class="summary-label">Total Duration</span>
                <span class="summary-value">{{ totalDuration }}ms</span>
              </div>
              <div class="summary-card">
                <span class="summary-label">Executed At</span>
                <span class="summary-value">{{ new Date(simulationResult.executed_at).toLocaleTimeString() }}</span>
              </div>
            </div>

            <!-- Node Results -->
            <div class="node-results">
              <h4>Node-by-Node Execution</h4>
              <div 
                v-for="(node, idx) in simulationResult.results" 
                :key="idx"
                :class="['node-result-card', node.status]"
              >
                <div class="node-header" @click="toggleNode(node.node_key)">
                  <div class="node-info">
                    <span class="node-icon">{{ getNodeIcon(node.status) }}</span>
                    <span class="node-key">{{ node.node_key }}</span>
                    <span class="node-status">{{ node.status }}</span>
                  </div>
                  <div class="node-meta">
                    <span class="node-duration">{{ node.duration_ms }}ms</span>
                    <span class="expand-icon">{{ expandedNodes[node.node_key] ? '▼' : '▶' }}</span>
                  </div>
                </div>

                <div v-if="expandedNodes[node.node_key]" class="node-details">
                  <div class="detail-section">
                    <h5>Input Data</h5>
                    <pre class="json-display">{{ formatJSON(node.input) }}</pre>
                  </div>

                  <div class="detail-section">
                    <h5>Output Data</h5>
                    <pre class="json-display">{{ formatJSON(node.output) }}</pre>
                  </div>

                  <div v-if="node.error" class="detail-section error">
                    <h5>Error Message</h5>
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
            <p>Simulation history will be displayed here.</p>
            <small>Feature coming soon: View past simulation runs and re-run with same data.</small>
          </div>
        </div>
      </div>

      <div class="simulation-footer">
        <div class="footer-info">
          <span v-if="isRunning" class="running-indicator">⚡ Running simulation...</span>
          <span v-else-if="simulationResult" class="result-indicator">
            ✓ Simulation completed
          </span>
        </div>
        <div class="footer-actions">
          <button @click="clearForm" class="btn-secondary" :disabled="isRunning">Clear</button>
          <button @click="$emit('close')" class="btn-secondary">Close</button>
          <button 
            @click="runSimulation" 
            class="btn-primary-gradient"
            :disabled="isRunning"
          >
            <span v-if="isRunning">⏳ Running...</span>
            <span v-else>🧪 Run Simulation</span>
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
  background: rgba(0, 0, 0, 0.5);
  z-index: 120;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.simulation-modal {
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  background: rgba(15, 23, 42, 0.95);
  border-radius: 20px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.simulation-header {
  padding: 20px 24px;
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

.simulation-header h3 {
  margin: 0;
  font-size: 18px;
  color: #a5b4fc;
  font-weight: 700;
}

.flow-badge {
  font-size: 11px;
  background: rgba(99, 102, 241, 0.2);
  padding: 4px 12px;
  border-radius: 40px;
  color: #818cf8;
  border: 1px solid rgba(99, 102, 241, 0.3);
  font-family: monospace;
}

.close-btn {
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 28px;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.simulation-tabs {
  display: flex;
  gap: 4px;
  padding: 12px 24px;
  background: rgba(0, 0, 0, 0.2);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.tab-btn {
  background: transparent;
  border: 1px solid transparent;
  color: #64748b;
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.tab-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.05);
  color: #94a3b8;
}

.tab-btn.active {
  background: rgba(99, 102, 241, 0.2);
  border-color: #6366f1;
  color: white;
}

.tab-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.simulation-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
}

/* Input Section */
.input-description {
  margin-bottom: 24px;
  padding: 16px;
  background: rgba(99, 102, 241, 0.1);
  border: 1px solid rgba(99, 102, 241, 0.2);
  border-radius: 12px;
}

.input-description p {
  margin: 0;
  color: #cbd5e1;
  font-size: 13px;
}

.input-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-field label {
  font-size: 13px;
  font-weight: 600;
  color: #e2e8f0;
}

.field-input {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 10px 14px;
  color: white;
  font-size: 14px;
  outline: none;
  transition: all 0.2s;
}

.field-input:focus {
  border-color: #6366f1;
  background: rgba(0, 0, 0, 0.4);
}

.custom-json-section {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.custom-json-section label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 8px;
}

.json-input {
  width: 100%;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 12px;
  color: white;
  font-size: 13px;
  font-family: monospace;
  resize: vertical;
  min-height: 80px;
  outline: none;
}

.custom-json-section small {
  display: block;
  margin-top: 6px;
  color: #64748b;
  font-size: 11px;
}

/* Results Section */
.results-summary {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 32px;
}

.summary-card {
  background: rgba(0, 0, 0, 0.2);
  padding: 16px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.summary-label {
  font-size: 11px;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 700;
}

.summary-value {
  font-size: 20px;
  font-weight: 800;
  color: white;
}

.node-results h4 {
  margin: 0 0 16px 0;
  font-size: 14px;
  color: #94a3b8;
  text-transform: uppercase;
  font-weight: 700;
}

.node-result-card {
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  margin-bottom: 12px;
  overflow: hidden;
}

.node-result-card.completed {
  border-left: 3px solid #10b981;
}

.node-result-card.failed {
  border-left: 3px solid #ef4444;
}

.node-header {
  padding: 14px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: background 0.2s;
}

.node-header:hover {
  background: rgba(255, 255, 255, 0.03);
}

.node-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.node-icon {
  font-size: 16px;
}

.node-key {
  font-family: monospace;
  font-size: 13px;
  color: #e2e8f0;
  font-weight: 600;
}

.node-status {
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.1);
  color: #94a3b8;
  text-transform: uppercase;
}

.node-meta {
  display: flex;
  align-items: center;
  gap: 12px;
}

.node-duration {
  font-size: 12px;
  color: #64748b;
  font-family: monospace;
}

.expand-icon {
  color: #64748b;
  font-size: 12px;
}

.node-details {
  padding: 16px;
  background: rgba(0, 0, 0, 0.3);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-section {
  margin-bottom: 16px;
}

.detail-section:last-child {
  margin-bottom: 0;
}

.detail-section h5 {
  margin: 0 0 8px 0;
  font-size: 11px;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 700;
}

.json-display {
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 12px;
  color: #cbd5e1;
  font-size: 12px;
  font-family: monospace;
  overflow-x: auto;
  margin: 0;
}

.error-display {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 8px;
  padding: 12px;
  color: #fca5a5;
  font-size: 12px;
  font-family: monospace;
  margin: 0;
}

/* History Section */
.history-placeholder {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

.history-placeholder p {
  margin: 0 0 8px 0;
  font-size: 14px;
}

.history-placeholder small {
  font-size: 12px;
  color: #475569;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

/* Footer */
.simulation-footer {
  padding: 16px 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(0, 0, 0, 0.2);
}

.footer-info {
  font-size: 12px;
}

.running-indicator {
  color: #fbbf24;
  font-weight: 600;
}

.result-indicator {
  color: #10b981;
  font-weight: 600;
}

.footer-actions {
  display: flex;
  gap: 12px;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #cbd5e1;
  padding: 10px 20px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary-gradient {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border: none;
  color: white;
  padding: 10px 24px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary-gradient:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
}

.btn-primary-gradient:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
