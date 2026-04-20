<script setup>
/**
 * FlowSimulationModal — Component for running flow simulations and viewing execution logs.
 */
import { ref } from 'vue'

const props = defineProps({
  show: Boolean,
  flowId: String,
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
    <div class="sim-modal">
      <header class="modal-header">
        <div class="header-title">
          <span>🧪</span>
          <h3>Flow Simulator (Dry Run)</h3>
        </div>
        <button class="close-btn" @click="emit('close')">×</button>
      </header>

      <div class="modal-body">
        <section class="config-section">
          <label>Trigger Payload (JSON)</label>
          <textarea v-model="triggerDataJson" rows="5" class="payload-input"></textarea>
          <button @click="runSimulation" :disabled="isSimulating" class="run-btn">
            {{ isSimulating ? '⌛ Simulating...' : '🚀 Start Simulation' }}
          </button>
        </section>

        <section v-if="simulationResult" class="result-section">
          <h4>Execution Path</h4>
          <div v-if="!simulationResult.success" class="error-msg">
            ❌ Simulation Error: {{ simulationResult.message }}
          </div>
          
          <div v-else class="path-timeline">
            <div 
              v-for="(step, idx) in simulationResult.path" 
              :key="idx" 
              class="path-item"
              :class="step.status"
            >
              <div class="step-icon">{{ step.status === 'completed' ? '✅' : '🔴' }}</div>
              <div class="step-details">
                <div class="step-key">{{ step.node_key }}</div>
                <div class="step-output" v-if="step.output">
                  <pre>{{ JSON.stringify(step.output, null, 2) }}</pre>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped>
.sim-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.8);
  backdrop-filter: blur(10px); z-index: 1000;
  display: flex; align-items: center; justify-content: center; padding: 40px;
}
.sim-modal {
  background: #0f172a; width: 100%; max-width: 800px;
  max-height: 80vh; border-radius: 24px; border: 1px solid rgba(255,255,255,0.1);
  display: flex; flex-direction: column; overflow: hidden;
}
.modal-header {
  padding: 16px 24px; display: flex; justify-content: space-between;
  align-items: center; border-bottom: 1px solid rgb(255 255 255 / 5%);
}
.header-title { display: flex; align-items: center; gap: 12px; }
.header-title h3 { margin: 0; font-size: 16px; color: #e2e8f0; }
.close-btn { background: none; border: none; color: #64748b; font-size: 24px; cursor: pointer; }

.modal-body { padding: 24px; overflow-y: auto; display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

.config-section label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 8px; }
.payload-input {
  width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 12px; color: #94a3b8; font-family: monospace; padding: 12px; font-size: 13px;
}
.run-btn {
  width: 100%; margin-top: 16px; padding: 12px; background: #6366f1; border: none;
  border-radius: 10px; color: white; font-weight: 600; cursor: pointer;
}

.path-timeline { border-left: 1px solid rgba(255,255,255,0.1); padding-left: 20px; }
.path-item { position: relative; margin-bottom: 24px; }
.path-item::before {
  content: ''; position: absolute; left: -24px; top: 0; width: 8px; height: 8px;
  border-radius: 50%; background: #64748b; border: 2px solid #0f172a;
}
.path-item.completed::before { background: #10b981; }
.path-item.failed::before { background: #ef4444; }

.step-key { font-size: 13px; font-weight: 700; color: #f1f5f9; margin-bottom: 4px; }
.step-output { background: rgba(0,0,0,0.2); padding: 8px; border-radius: 8px; font-size: 11px; color: #94a3b8; }
.step-output pre { margin: 0; white-space: pre-wrap; }
</style>
