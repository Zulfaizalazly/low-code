<script setup>
/**
 * FlowSimulationModal — Component for running flow simulations and viewing execution logs.
 */
import { ref } from 'vue'

const props = defineProps({
  show: Boolean,
  flowId: [String, Number],
  versionId: [String, Number],
  flowKey: String
})

const emit = defineEmits(['close'])

const isSimulating = ref(false)
const simulationResult = ref(null)
const triggerDataJson = ref('{\n  "amount": 5000,\n  "customer_id": 1\n}')

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
</script>

<template>
  <div v-if="show" class="sim-overlay" @click.self="emit('close')">
    <div class="sim-modal liquid-glass">
      <header class="modal-header">
        <div class="header-title">
          <div class="icon-pulse">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
              <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
          </div>
          <h3>Flow Simulation Engine</h3>
        </div>
        <button class="close-btn" @click="emit('close')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </header>

      <div class="modal-body custom-scroll">
        <div class="body-grid">
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
      </div>
    </div>
  </div>
</template>

<style scoped>
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
  max-width: 900px;
  max-height: 85vh;
  border-radius: 28px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  color: #f1f5f9;
}

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
}

.result-side {
  padding: 32px;
  background: rgba(15, 23, 42, 0.2);
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

/* Timeline */
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

.item-output pre {
  margin: 0;
  white-space: pre-wrap;
  font-family: inherit;
}

/* Empty State */
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

/* Error Card */
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

/* Custom Scroll */
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

/* Shimmer */
.shimmer-path {
  display: flex;
  flex-direction: column;
  gap: 16px;
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
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
