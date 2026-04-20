<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { VueFlow, useVueFlow, Position, MarkerType } from '@vue-flow/core'
import { Background } from '@vue-flow/background'
import { Controls } from '@vue-flow/controls'
import NodePalette from './NodePalette.vue'
import NodeInspector from './NodeInspector.vue'
import EdgeInspector from './EdgeInspector.vue'
import AIPreviewModal from './AIPreviewModal.vue'
import FlowSimulationModal from './FlowSimulationModal.vue'

// ... in script setup ...
function onEdgeClick(event) {
  selectedNode.value = null
  const edge = event.edge
  selectedEdge.value = {
    id: edge.id,
    source: edge.source,
    target: edge.target,
    conditionType: edge.data?.conditionType || 'always',
    conditionConfig: { ...edge.data?.conditionConfig },
  }
}

function updateEdgeConfig(update) {
  const edgeIdx = edges.value.findIndex(e => e.id === update.id)
  if (edgeIdx !== -1) {
    edges.value[edgeIdx].data = {
      ...edges.value[edgeIdx].data,
      conditionType: update.conditionType,
      conditionConfig: { ...update.conditionConfig },
    }
    isDirty.value = true
  }
}

const props = defineProps({
  flowId: String,
  versionId: [String, Number],
  flowKey: String,
  initialNodes: { type: Array, default: () => [] },
  initialEdges: { type: Array, default: () => [] },
  commands: { type: Array, default: () => [] },
})

const { onPaneReady, onConnect, addEdges, removeNodes, toObject, project } = useVueFlow()

const nodes = ref([])
const edges = ref([])
const selectedNode = ref(null)
const selectedEdge = ref(null)
const isDirty = ref(false)
const isGenerating = ref(false)
const showAIPreview = ref(false)
const showSimulation = ref(false)
const iterationCount = ref(0)

// ─── Keyboard Shortcuts & Initialization ───
onMounted(() => {
    // ... existing init code ...
    window.addEventListener('keydown', handleKeyDown)

    // Auto-save every 30 seconds...
    setInterval(() => {
        if (isDirty.value && !isGenerating.value) {
            saveFlow()
        }
    }, 30000)
})

function handleKeyDown(e) {
    // Ctrl+S or Cmd+S to Save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault()
        saveFlow()
    }
    // Delete key to remove selected node/edge
    if (e.key === 'Delete' || e.key === 'Backspace') {
        // Only if not typing in an input/textarea
        if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return
        
        if (selectedNode.value) {
            deleteNode(selectedNode.value.id)
        }
    }
}
const generatedUIData = ref({
  definition: null,
  aspects: [],
  options: {}
})

// ─── Node Type Styling ───
const nodeColors = {
  trigger: { bg: '#0f766e', border: '#14b8a6', icon: '⚡' },
  command: { bg: '#1e40af', border: '#3b82f6', icon: '⚙️' },
  decision: { bg: '#92400e', border: '#f59e0b', icon: '🔀' },
  approval: { bg: '#7e22ce', border: '#a855f7', icon: '✅' },
  notification: { bg: '#0e7490', border: '#06b6d4', icon: '📧' },
  document: { bg: '#9a3412', border: '#f97316', icon: '📄' },
  gl_action: { bg: '#166534', border: '#22c55e', icon: '💰' },
  formula: { bg: '#4338ca', border: '#818cf8', icon: '🔢' },
  end: { bg: '#991b1b', border: '#ef4444', icon: '🏁' },
}

// ─── Initialize from Laravel data ───
onMounted(() => {
  nodes.value = props.initialNodes.map(node => ({
    id: String(node.id || node.node_key),
    type: node.node_type === 'trigger' ? 'input' : (node.node_type === 'end' ? 'output' : 'default'),
    position: { x: node.position_x || 0, y: node.position_y || 0 },
    data: {
      label: node.label,
      nodeKey: node.node_key,
      nodeType: node.node_type,
      config: node.config || {},
      color: nodeColors[node.node_type] || nodeColors.command,
    },
    style: getNodeStyle(node.node_type),
    sourcePosition: Position.Bottom,
    targetPosition: Position.Top,
  }))

  edges.value = props.initialEdges.map(edge => ({
    id: `e-${edge.id}`,
    source: String(edge.source_node_id || edge.source_node_key),
    target: String(edge.target_node_id || edge.target_node_key),
    animated: true,
    style: { stroke: '#6366f1', strokeWidth: 2 },
    markerEnd: MarkerType.ArrowClosed,
    data: {
      conditionType: edge.condition_type || 'always',
      conditionConfig: edge.condition_config || {},
    },
  }))
})

function getNodeStyle(nodeType) {
  const color = nodeColors[nodeType] || nodeColors.command
  return {
    background: color.bg,
    border: `2px solid ${color.border}`,
    color: '#fff',
    borderRadius: '12px',
    padding: '12px 16px',
    fontSize: '13px',
    fontWeight: '500',
    backdropFilter: 'blur(8px)',
    boxShadow: `0 4px 20px ${color.border}22`,
    minWidth: '150px',
    textAlign: 'center',
  }
}

// ─── Handle Connections ───
onConnect((params) => {
  addEdges([{
    ...params,
    animated: true,
    style: { stroke: '#6366f1', strokeWidth: 2 },
    markerEnd: MarkerType.ArrowClosed,
    data: { conditionType: 'always', conditionConfig: {} },
  }])
  isDirty.value = true
})

// ─── Node Selection ───
function onNodeClick(event) {
  const node = event.node
  selectedNode.value = {
    id: node.id,
    label: node.data.label,
    nodeKey: node.data.nodeKey,
    nodeType: node.data.nodeType,
    config: { ...node.data.config },
  }
}

function onPaneClick() {
  selectedNode.value = null
}

// ─── Update Node Config from Inspector ───
function updateNodeConfig(update) {
  const nodeIdx = nodes.value.findIndex(n => n.id === update.id)
  if (nodeIdx !== -1) {
    nodes.value[nodeIdx].data = {
      ...nodes.value[nodeIdx].data,
      label: update.label,
      config: { ...update.config },
    }
    isDirty.value = true
  }
}

// ─── Delete Node ───
function deleteNode(nodeId) {
  nodes.value = nodes.value.filter(n => n.id !== nodeId)
  edges.value = edges.value.filter(e => e.source !== nodeId && e.target !== nodeId)
  selectedNode.value = null
  isDirty.value = true
}

// ─── Drag and Drop from Palette ───
let nextId = 100

function onDrop(event) {
  const nodeType = event.dataTransfer.getData('application/nodeType')
  if (!nodeType) return

  const { left, top } = event.target.closest('.vue-flow')?.getBoundingClientRect() || {}
  const position = project({
    x: event.clientX - (left || 0),
    y: event.clientY - (top || 0),
  })

  const id = `new_${nextId++}`
  const color = nodeColors[nodeType] || nodeColors.command

  nodes.value.push({
    id,
    type: nodeType === 'trigger' ? 'input' : (nodeType === 'end' ? 'output' : 'default'),
    position,
    data: {
      label: `${color.icon} ${nodeType.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}`,
      nodeKey: id,
      nodeType: nodeType,
      config: {},
      color,
    },
    style: getNodeStyle(nodeType),
    sourcePosition: Position.Bottom,
    targetPosition: Position.Top,
  })
  isDirty.value = true
}

function onDragOver(event) {
  event.preventDefault()
  event.dataTransfer.dropEffect = 'move'
}

// ─── Save to Livewire ───
function saveFlow() {
  const flowObject = toObject()

  const serializedNodes = flowObject.nodes.map(node => ({
    node_key: node.data.nodeKey,
    node_type: node.data.nodeType,
    label: node.data.label,
    config: node.data.config,
    position_x: Math.round(node.position.x),
    position_y: Math.round(node.position.y),
  }))

  const serializedEdges = flowObject.edges.map(edge => ({
    source_node_key: edge.source,
    target_node_key: edge.target,
    condition_type: edge.data?.conditionType || 'always',
    condition_config: edge.data?.conditionConfig || {},
  }))

  window.dispatchEvent(new CustomEvent('vue-flow-save', {
    detail: { nodes: serializedNodes, edges: serializedEdges }
  }))

  isDirty.value = false
}

window.saveFlowToLivewire = saveFlow

// ─── AI UI Generation ───
function triggerAIGeneration() {
  if (isDirty.value) {
    if (!confirm('You have unsaved changes. Save now and generate UI?')) return
    saveFlow()
  }
  
  isGenerating.value = true
  // Call Livewire component method via global bridge or standard Livewire dispatch
  if (window.Livewire) {
    window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).generateUI()
  }
}

window.addEventListener('ui-generated', (event) => {
  isGenerating.value = false
  iterationCount.value = 0 // Reset for new session
  generatedUIData.value = event.detail[0]
  showAIPreview.value = true
})

window.addEventListener('ui-refined', (event) => {
  iterationCount.value++
  generatedUIData.value = event.detail[0]
})

window.addEventListener('ui-generation-failed', (event) => {
  isGenerating.value = false
  if (confirm('Generation failed: ' + event.detail.message + '\n\nWould you like to report this issue to Arrahnumation support?')) {
    window.open('/studio/support/report?context=ai_generation_fail&msg=' + encodeURIComponent(event.detail.message))
  }
})

function publishAIUI() {
  if (confirm('Are you sure you want to publish this UI definition to the registry?')) {
    // Send final definition to Livewire for persistence
    if (window.Livewire) {
      window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
        .publishAIUI(generatedUIData.value.definition)
    }
    showAIPreview.value = false
  }
}

function handleManualOverride() {
  if (confirm('This will open the manual Page Builder with the generated definition. Continue?')) {
    // Save to Livewire first to create the draft record
    if (window.Livewire) {
      window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
        .createAIDraft(generatedUIData.value.definition)
        .then(pageId => {
          if (pageId) {
            window.location.href = `/studio/pages/builder/${pageId}?from_ai=1`
          }
        })
    }
  }
}

// ─── Validation Indicators ───
const validationWarnings = computed(() => {
  const warnings = []
  const hasTrigger = nodes.value.some(n => n.data.nodeType === 'trigger')
  const hasEnd = nodes.value.some(n => n.data.nodeType === 'end')

  if (!hasTrigger) warnings.push({ type: 'error', message: 'Missing trigger node' })
  if (!hasEnd) warnings.push({ type: 'error', message: 'Missing end node' })

  // Check for orphan nodes and config completeness
  nodes.value.forEach(node => {
    // Connection check
    if (node.data.nodeType !== 'trigger' && node.data.nodeType !== 'end') {
      const hasIncoming = edges.value.some(e => e.target === node.id)
      const hasOutgoing = edges.value.some(e => e.source === node.id)
      if (!hasIncoming && !hasOutgoing) {
        warnings.push({ type: 'warning', node: node.id, message: `Node "${node.data.label}" is disconnected` })
      }
    }

    // Config completion check
    const config = node.data.config || {}
    const nodeType = node.data.nodeType
    
    if (nodeType === 'command' && !config.command_class) {
      warnings.push({ type: 'error', node: node.id, message: `Node "${node.data.label}" missing command class` })
    }
    if (nodeType === 'decision' && !config.expression && !config.condition_field) {
      warnings.push({ type: 'error', node: node.id, message: `Node "${node.data.label}" missing condition/expression` })
    }
    if (nodeType === 'notification' && !config.channel) {
      warnings.push({ type: 'warning', node: node.id, message: `Node "${node.data.label}" missing notification channel` })
    }
    if (nodeType === 'formula' && !config.formula_key) {
      warnings.push({ type: 'error', node: node.id, message: `Node "${node.data.label}" missing formula reference` })
    }
  })

  return warnings
})

const hasErrors = computed(() => validationWarnings.value.some(w => w.type === 'error'))
const showValidationDetails = ref(false)

async function submitForReview() {
  if (isDirty.value) {
    if (!confirm('You have unsaved changes. Save now before submitting?')) return
    await saveFlow()
  }

  if (hasErrors.value) {
    alert('Please fix validation errors before submitting for review.')
    return
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
  <div class="flow-builder-container">
    <!-- Left: Node Palette -->
    <NodePalette />

    <!-- Center: Canvas -->
    <div class="flow-canvas-wrapper" @drop="onDrop" @dragover="onDragOver">
      <!-- Toolbar -->
      <div class="canvas-toolbar">
        <button 
          @click="saveFlow" 
          class="toolbar-btn primary-btn"
          :disabled="hasErrors"
        >
          💾 Save Flow
        </button>

        <button 
          @click="showValidationDetails = !showValidationDetails" 
          class="toolbar-btn"
          :class="{ 'warning-btn': validationWarnings.length > 0 }"
        >
          🔍 Validate ({{ validationWarnings.length }})
        </button>

        <button 
          @click="showSimulation = true" 
          class="toolbar-btn"
        >
          🧪 Simulate Flow
        </button>

        <button 
          @click="submitForReview" 
          class="toolbar-btn primary-btn"
          style="background: #1e3a8a; border-color: #3b82f6;"
        >
          🕵️ Submit for Review
        </button>

        <div class="toolbar-divider"></div>

        <button 
          @click="triggerAIGeneration" 
          class="ai-gen-btn"
          :disabled="isGenerating || nodes.length < 2"
        >
          <span v-if="isGenerating">✨ Generating...</span>
          <span v-else>✨ AI Generate UI</span>
        </button>
      </div>

      <!-- Validation Sidebar/Popup -->
      <div v-if="showValidationDetails" class="validation-panel">
        <div class="panel-header">
          <h4>Validation Issues</h4>
          <button @click="showValidationDetails = false">×</button>
        </div>
        <div class="panel-content">
          <div v-if="validationWarnings.length === 0" class="empty-notif">
            ✅ No issues found
          </div>
          <div 
            v-for="(w, i) in validationWarnings" 
            :key="i" 
            class="v-item" 
            :class="w.type"
          >
            <span class="v-icon">{{ w.type === 'error' ? '🔴' : '⚠️' }}</span>
            <span class="v-msg">{{ w.message }}</span>
          </div>
        </div>
      </div>

      <!-- Validation Banner (Simplified) -->
      <div v-if="validationWarnings.length && !showValidationDetails" class="validation-banner" @click="showValidationDetails = true">
        <span>⚠️</span>
        <span>{{ validationWarnings.length }} issues detected</span>
      </div>

      <!-- Dirty Indicator -->
      <div v-if="isDirty" class="dirty-indicator">
        <span>● Unsaved changes</span>
      </div>

      <VueFlow
        v-model:nodes="nodes"
        v-model:edges="edges"
        @node-click="onNodeClick"
        @edge-click="onEdgeClick"
        @pane-click="onPaneClick"
        @dragover="onDragOver"
        @drop="onDrop"
        :default-viewport="{ zoom: 1.2 }"
        :min-zoom="0.2"
        :max-zoom="4"
        class="vue-flow-canvas"
      >
        <Background pattern-color="#334155" :gap="16" />
        <Controls />
      </VueFlow>
    </div>

    <!-- Right: Node Inspector -->
    <NodeInspector
      :node="selectedNode"
      :commands="commands"
      @update="updateNodeConfig"
      @delete="deleteNode"
    />

    <EdgeInspector
      v-if="selectedEdge"
      :edge="selectedEdge"
      @update="updateEdgeConfig"
    />

    <!-- AI Preview & Refinement -->
    <AIPreviewModal
      :show="showAIPreview"
      :definition="generatedUIData.definition"
      :aspects="generatedUIData.aspects"
      :options="generatedUIData.options"
      :iteration-count="iterationCount"
      @close="showAIPreview = false"
      @publish="publishAIUI"
      @manual-override="handleManualOverride"
    />

    <!-- Simulation Modal -->
    <SimulationModal
      v-if="showSimulation"
      :show="showSimulation"
      :version-id="versionId"
      :flow-key="flowKey"
      @close="showSimulation = false"
    />
  </div>
</template>

<style>
@import '@vue-flow/core/dist/style.css';
@import '@vue-flow/core/dist/theme-default.css';
@import '@vue-flow/controls/dist/style.css';

.flow-builder-container {
  display: flex;
  height: 100%;
  width: 100%;
  gap: 0;
  background: #0f172a;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(255,255,255,0.05);
}

.flow-canvas-wrapper {
  flex: 1;
  position: relative;
  min-height: 600px;
}

.ai-gen-btn, .toolbar-btn {
  background: rgba(30, 41, 59, 0.7);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 8px 14px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
  backdrop-filter: blur(8px);
}

.primary-btn {
  background: #6366f1;
  border-color: #818cf8;
}
.primary-btn:hover:not(:disabled) {
  background: #4f46e5;
}
.primary-btn:disabled {
  background: #334155;
  opacity: 0.5;
}

.warning-btn {
  border-color: rgba(234, 179, 8, 0.5);
  color: #fbbf24;
}

.toolbar-divider {
  width: 1px;
  height: 24px;
  background: rgba(255, 255, 255, 0.1);
  margin: 0 4px;
}

.ai-gen-btn {
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  border: none;
  box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
}

.ai-gen-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
}

.ai-gen-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background: #334155;
  box-shadow: none;
}

.arrahnu-flow-canvas {
  background-color: #0f172a;
  height: 100%;
}

.validation-banner {
  position: absolute;
  top: 12px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
  display: flex;
  gap: 8px;
  align-items: center;
  padding: 6px 14px;
  background: rgba(234, 179, 8, 0.15);
  border: 1px solid rgba(234, 179, 8, 0.3);
  border-radius: 10px;
  backdrop-filter: blur(12px);
  font-size: 12px;
  color: #fbbf24;
  cursor: pointer;
}

.validation-panel {
  position: absolute;
  top: 60px;
  right: 12px;
  width: 300px;
  max-height: 400px;
  background: rgba(15, 23, 42, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  z-index: 30;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 40px rgba(0,0,0,0.4);
  backdrop-filter: blur(20px);
}

.panel-header {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.panel-header h4 { margin: 0; font-size: 13px; color: #e2e8f0; }
.panel-header button { background: none; border: none; color: #64748b; cursor: pointer; font-size: 18px; }

.panel-content {
  padding: 8px;
  overflow-y: auto;
}

.v-item {
  display: flex;
  gap: 10px;
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 4px;
  font-size: 12px;
}
.v-item.error { background: rgba(239, 68, 68, 0.1); color: #f87171; }
.v-item.warning { background: rgba(234, 179, 8, 0.1); color: #fbbf24; }
.empty-notif { padding: 20px; text-align: center; color: #94a3b8; font-size: 13px; }

.warning-badge {
  padding: 2px 8px;
  background: rgba(234, 179, 8, 0.1);
  border-radius: 6px;
  font-size: 11px;
}

.dirty-indicator {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 10;
  padding: 4px 12px;
  background: rgba(99, 102, 241, 0.2);
  border: 1px solid rgba(99, 102, 241, 0.3);
  border-radius: 8px;
  font-size: 11px;
  color: #a5b4fc;
  backdrop-filter: blur(12px);
}

.vue-flow__node {
  transition: box-shadow 0.2s, transform 0.15s;
}
.vue-flow__node:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 30px rgba(99, 102, 241, 0.15);
}
.vue-flow__node.selected {
  box-shadow: 0 0 0 2px #6366f1, 0 8px 30px rgba(99, 102, 241, 0.2);
}

.vue-flow__edge-path {
  stroke: #6366f1;
  stroke-width: 2;
}

.vue-flow__controls {
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px;
  backdrop-filter: blur(12px);
}
.vue-flow__controls-button {
  background: transparent;
  border: none;
  color: #94a3b8;
  fill: #94a3b8;
}
.vue-flow__controls-button:hover {
  background: rgba(255,255,255,0.05);
  color: #e2e8f0;
  fill: #e2e8f0;
}
</style>
