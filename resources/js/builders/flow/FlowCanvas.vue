<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { VueFlow, useVueFlow, Position, MarkerType, Handle } from '@vue-flow/core'
import { Background } from '@vue-flow/background'
import { Controls } from '@vue-flow/controls'
import { useToast } from 'vue-toastification'
import NodePalette from './NodePalette.vue'
import NodeInspector from './NodeInspector.vue'
import EdgeInspector from './EdgeInspector.vue'
import AIPreviewModal from './AIPreviewModal.vue'
import FlowSimulationModal from './FlowSimulationModal.vue'

const toast = useToast()

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
const isFullscreen = ref(false)
const generatedPageUrl = ref('')
const toastMessage = ref('')
const toastType = ref('success')

function showToast(msg, type = 'success') {
  toastMessage.value = msg;
  toastType.value = type;
  setTimeout(() => { toastMessage.value = '' }, 5000);
}

function goBack() {
  if (isDirty.value) {
    if (!confirm('Any unsaved changes will be lost. Go back?')) return
  }
  window.history.back()
}

function toggleFullscreen() {
    const element = document.querySelector('.flow-builder-container')
    
    if (!isFullscreen.value) {
        if (element.requestFullscreen) {
            element.requestFullscreen().catch(err => {
                console.error(`Error attempting to enable fullscreen: ${err.message}`)
                isFullscreen.value = true // Fallback to CSS fullscreen
            })
        } else {
            isFullscreen.value = true
        }
    } else {
        if (document.fullscreenElement) {
            document.exitFullscreen()
        }
        isFullscreen.value = false
    }

    // trigger resize so vue flow recalculates its canvas bounds
    setTimeout(() => window.dispatchEvent(new Event('resize')), 100)
}

// Watch for native fullscreen changes
if (typeof document !== 'undefined') {
    document.addEventListener('fullscreenchange', () => {
        isFullscreen.value = !!document.fullscreenElement
        setTimeout(() => window.dispatchEvent(new Event('resize')), 100)
    })
}

// SVG Icons
const icons = {
  trigger: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
  command: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
  approval: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
  notification: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
  document: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
  gl_action: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><path d="M12 12h.01"></path><path d="M17 12h.01"></path><path d="M7 12h.01"></path></svg>',
  decision: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 3 21 3 21 8"></polyline><line x1="4" y1="20" x2="21" y2="3"></line><polyline points="21 16 21 21 16 21"></polyline><line x1="15" y1="15" x2="21" y2="21"></line><line x1="4" y1="4" x2="9" y2="9"></line></svg>',
  formula: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="15"></line><line x1="15" y1="9" x2="9" y2="15"></line></svg>',
  end: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><rect x="9" y="9" width="6" height="6"></rect></svg>'
}

const nodeThemeColors = {
  trigger: '#34c759',    // Green
  command: '#007aff',    // Blue
  decision: '#ff9500',   // Orange
  approval: '#af52de',   // Purple
  notification: '#5ac8fa', // Light Blue
  document: '#ff3b30',   // Red
  gl_action: '#34c759',  // Green
  formula: '#5856d6',    // Indigo
  end: '#ff3b30'         // Red
}

// ─── Keyboard Shortcuts & Initialization ───
onMounted(() => {
    // Initialize nodes from Laravel data
    nodes.value = props.initialNodes.map(node => {
      const cleanLabel = node.label.replace(/[\u2700-\u27BF]|[\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|[\u2011-\u26FF]|\uD83E[\uDD10-\uDDFF]/g, '').trim()
      
      return {
        id: String(node.id || node.node_key),
        type: node.node_type === 'trigger' ? 'input' : (node.node_type === 'end' ? 'output' : 'default'),
        position: { x: node.position_x || 0, y: node.position_y || 0 },
        data: {
            label: node.label, // original fallback
            cleanLabel: cleanLabel || node.node_type,
            icon: icons[node.node_type] || icons.command,
            themeColor: nodeThemeColors[node.node_type] || '#000000',
            nodeKey: node.node_key,
            nodeType: node.node_type,
            config: node.config || {},
        },
        style: getNodeStyle(node.node_type),
        sourcePosition: Position.Bottom,
        targetPosition: Position.Top,
      }
    })

    edges.value = props.initialEdges.map(edge => ({
        id: `e-${edge.id}`,
        source: String(edge.source_node_id || edge.source_node_key),
        target: String(edge.target_node_id || edge.target_node_key),
        animated: true,
        style: { stroke: '#007aff', strokeWidth: 2 },
        markerEnd: MarkerType.ArrowClosed,
        data: {
            conditionType: edge.condition_type || 'always',
            conditionConfig: edge.condition_config || {},
        },
    }))

    // Keyboard shortcuts
    window.addEventListener('keydown', handleKeyDown)

    // Auto-save every 30 seconds
    setInterval(() => {
        if (isDirty.value && !isGenerating.value) {
            saveFlow()
        }
    }, 30000)

    // Browser navigation protection
    window.onbeforeunload = (e) => {
        if (isDirty.value) {
            e.preventDefault()
            e.returnValue = ''
        }
    }
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown)
    window.onbeforeunload = null
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

// (Node and edge initialization is handled in onMounted above)

function getNodeStyle(nodeType) {
  // We use custom nodes, so root node wrapper just needs minimal transparent style
  // so Vue Flow doesn't impose default paddings that mess up our design.
  return {
    padding: '0px',
    border: 'none',
    background: 'transparent',
    boxShadow: 'none',
    minWidth: '160px'
  }
}

// ─── Handle Connections ───
onConnect((params) => {
  addEdges([{
    ...params,
    animated: true,
    style: { stroke: '#007aff', strokeWidth: 2 },
    markerEnd: MarkerType.ArrowClosed,
    data: { conditionType: 'always', conditionConfig: {} },
  }])
  isDirty.value = true
})

// ─── Node Selection ───
function onNodeClick(event) {
  selectedEdge.value = null
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
  selectedEdge.value = null
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

function onDrop(event) {
  const nodeType = event.dataTransfer.getData('application/nodeType')
  if (!nodeType) return

  const { left, top } = event.target.closest('.vue-flow')?.getBoundingClientRect() || {}
  const position = project({
    x: event.clientX - (left || 0),
    y: event.clientY - (top || 0),
  })

  // Generate robust random ID to prevent collisions with existing saved node keys
  const id = 'n_' + Date.now().toString(36) + Math.random().toString(36).substring(2, 6)
  const theme = nodeThemeColors[nodeType] || '#000000'

  nodes.value.push({
    id,
    type: nodeType === 'trigger' ? 'input' : (nodeType === 'end' ? 'output' : 'default'),
    position,
    data: {
      label: `New ${nodeType}`,
      cleanLabel: nodeType.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()),
      icon: icons[nodeType] || icons.command,
      themeColor: theme,
      nodeKey: id,
      nodeType: nodeType,
      config: {},
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
  return new Promise((resolve) => {
    const flowObject = toObject()

    const serializedNodes = flowObject.nodes.map(node => ({
        node_key: node.data.nodeKey,
        node_type: node.data.nodeType,
        label: node.data.cleanLabel || node.data.label,
        config: node.data.config,
        position_x: Math.round(node.position.x),
        position_y: Math.round(node.position.y),
    }))

    const serializedEdges = flowObject.edges.map(edge => {
        const sourceNode = flowObject.nodes.find(n => n.id === edge.source)
        const targetNode = flowObject.nodes.find(n => n.id === edge.target)

        return {
            source_node_key: sourceNode ? sourceNode.data.nodeKey : edge.source,
            target_node_key: targetNode ? targetNode.data.nodeKey : edge.target,
            condition_type: edge.data?.conditionType || 'always',
            condition_config: edge.data?.conditionConfig || {},
        }
    })

    // Listen for the return event from Livewire to resolve the promise
    const onSaved = () => {
        window.removeEventListener('flow-saved', onSaved)
        toast.success('Flow definition updated successfully')
        resolve()
    }
    window.addEventListener('flow-saved', onSaved)

    window.dispatchEvent(new CustomEvent('vue-flow-save', {
        detail: { nodes: serializedNodes, edges: serializedEdges }
    }))

    isDirty.value = false
  })
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
  const msg = event.detail[0]?.message || event.detail?.message || 'Unknown error';
  if (confirm('Generation failed: ' + msg + '\n\nWould you like to report this issue to Arrahnumation support?')) {
    window.open('/studio/support/report?context=ai_generation_fail&msg=' + encodeURIComponent(msg))
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

window.addEventListener('ui-published', (event) => {
  const payload = event.detail[0] || event.detail;
  if (payload && payload.url) {
    generatedPageUrl.value = payload.url;
    showToast('Success! UI has been published to the registry.');
  } else {
    showToast('UI has been successfully submitted.');
  }
})

function viewGeneratedUI() {
  if (generatedPageUrl.value) {
    window.location.href = generatedPageUrl.value;
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
            generatedPageUrl.value = `/studio/pages/builder/${pageId}?from_ai=1`
            window.location.href = generatedPageUrl.value
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
           'Content-Type': 'application/json',
           'Accept': 'application/json'
         }
      })
      
      const data = await res.json()
      
      if (res.ok && data.success) {
        alert('Submitted successfully!')
        window.location.href = '/studio/releases'
      } else {
        alert('Submission failed: ' + (data.message || 'Unknown server error'))
      }
    } catch (error) {
      alert('Error submitting: ' + error.message)
    }
  }
}
</script>

<template>
  <div class="flow-builder-container" :class="{ 'fullscreen-mode': isFullscreen }">
    <!-- Left: Node Palette -->
    <NodePalette />

    <!-- Center: Canvas -->
    <div class="flow-canvas-wrapper">
      <!-- Toolbar (Apple Island Style) -->
      <div class="canvas-island-toolbar">
        <button @click="goBack" class="island-btn" title="Back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>

        <div class="island-divider"></div>

        <button @click="toggleFullscreen" class="island-btn" title="Toggle Fullscreen">
          <svg v-if="isFullscreen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path></svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
        </button>

        <div class="island-divider"></div>

        <button @click="saveFlow" class="island-btn primary" :disabled="hasErrors">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          Save
        </button>

        <button @click="showValidationDetails = !showValidationDetails" 
                class="island-btn" 
                :class="{ 'warning-glow': validationWarnings.length > 0, 'active': showValidationDetails }">
           <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
             <circle cx="11" cy="11" r="8"></circle>
             <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
           </svg>
           <span v-if="hasErrors" class="text-red-500 font-bold">Issues Found</span>
           <span v-else>Validate</span>
           <span v-if="validationWarnings.length" class="badge-pulse">{{ validationWarnings.length }}</span>
        </button>

        <button @click="showSimulation = true" class="island-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
          Simulate
        </button>

        <button @click="submitForReview" class="island-btn submit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          Submit
        </button>

        <div class="island-divider"></div>

        <button v-if="generatedPageUrl" @click="viewGeneratedUI" class="island-btn view-generated" title="View Generated UI">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          View UI
        </button>

        <button @click="triggerAIGeneration" class="island-btn magic" :disabled="isGenerating || nodes.length < 2">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
          <span v-if="isGenerating">Building...</span>
          <span v-else>{{ generatedPageUrl ? 'Re-Gen UI' : 'Gen-UI' }}</span>
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

      <!-- Toast Notification -->
      <transition name="fade">
        <div v-if="toastMessage" class="mac-toast" :class="toastType">
          <span class="v-icon">{{ toastType === 'success' ? '✅' : '⚠️' }}</span>
          <span>{{ toastMessage }}</span>
        </div>
      </transition>

      <VueFlow
        v-model:nodes="nodes"
        v-model:edges="edges"
        @node-click="onNodeClick"
        @edge-click="onEdgeClick"
        @pane-click="onPaneClick"
        @dragover="onDragOver"
        @drop="onDrop"
        @node-drag-stop="isDirty = true"
        :default-viewport="{ zoom: 1 }"
        :min-zoom="0.2"
        :max-zoom="4"
        class="vue-flow-canvas"
      >
        <!-- CUSTOM NODE PROXIES -->
        <template #node-input="{ data }">
          <div class="mac-node" :style="{ '--theme-color': data.themeColor }">
            <span class="mac-node-icon" v-html="data.icon"></span>
            <span class="mac-node-title">{{ data.cleanLabel }}</span>
          </div>
          <Handle type="source" position="bottom" class="mac-handle" />
        </template>

        <template #node-default="{ data }">
          <Handle type="target" position="top" class="mac-handle top" />
          <div class="mac-node" :style="{ '--theme-color': data.themeColor }">
            <span class="mac-node-icon" v-html="data.icon"></span>
            <span class="mac-node-title">{{ data.cleanLabel }}</span>
          </div>
          <Handle type="source" position="bottom" class="mac-handle" />
        </template>

        <template #node-output="{ data }">
          <Handle type="target" position="top" class="mac-handle top" />
          <div class="mac-node" :style="{ '--theme-color': data.themeColor }">
            <span class="mac-node-icon" v-html="data.icon"></span>
            <span class="mac-node-title">{{ data.cleanLabel }}</span>
          </div>
        </template>

        <Background pattern-color="#e5e5ea" :gap="20" />
        <Controls class="mac-controls" />
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
    <FlowSimulationModal
      v-if="showSimulation"
      :show="showSimulation"
      :flow-id="flowId"
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
  background: transparent;
  border-radius: 0;
  overflow: hidden;
  border: none;
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease, border-radius 0.4s ease;
  font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Inter", sans-serif;
}

.flow-builder-container.fullscreen-mode, .flow-builder-container:fullscreen {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100vw;
  height: 100vh;
  z-index: 99999;
  border-radius: 0;
  border: none;
  background: #f5f5f7; /* Ensure background is set in fullscreen */
}

.flow-canvas-wrapper {
  flex: 1;
  position: relative;
  min-height: 600px;
}

/* Apple Island Toolbar */
.canvas-island-toolbar {
  position: absolute;
  top: 24px;
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
  transition: all 0.3s ease;
}

.island-btn {
  background: transparent;
  border: none;
  min-height: 28px;
  min-width: 28px;
  padding: 0 10px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  color: #1d1d1f;
  font-size: 13px;
  font-weight: 500;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.island-btn:hover { background: rgba(0,0,0,0.04); }
.island-btn:active { transform: scale(0.96); }
.island-btn.primary {
  background: #007aff;
  color: white;
}
.island-btn.primary:hover { background: #0066d6; }

.island-btn.magic {
  background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(236, 72, 153, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
.island-btn.magic:hover {
  background: linear-gradient(135deg, #9333ea 0%, #db2777 100%);
  box-shadow: 0 6px 16px rgba(236, 72, 153, 0.35);
}

.island-btn.view-generated {
  background: rgba(255, 255, 255, 0.9);
  color: #007aff;
  border: 1px solid rgba(0, 122, 255, 0.2);
  box-shadow: 0 2px 6px rgba(0, 122, 255, 0.1);
}
.island-btn.view-generated:hover {
  background: #f0f7ff;
  border-color: rgba(0, 122, 255, 0.3);
}

.island-btn.submit {
  background: #34c759;
  color: white;
}
.island-btn.submit:hover { background: #2ebd4e; }

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

.island-divider {
  width: 1px;
  height: 20px;
  background: rgba(0,0,0,0.1);
  margin: 0 4px;
}

.badge-pulse {
  background: #ff3b30;
  color: white;
  font-size: 10px;
  font-weight: bold;
  padding: 1px 6px;
  border-radius: 10px;
  animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
  0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 59, 48, 0.7); }
  70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(255, 59, 48, 0); }
  100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 59, 48, 0); }
}

.island-btn.warning-glow {
  box-shadow: 0 0 12px rgba(255, 59, 48, 0.2);
  border: 1px solid rgba(255, 59, 48, 0.3);
}

/* Custom Nodes Apple Style */
.mac-node {
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(0,0,0,0.06);
  border-radius: 14px;
  padding: 12px 16px;
  min-width: 140px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.04);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  position: relative;
}

.mac-node::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  border-radius: 14px;
  border: 2px solid var(--theme-color);
  opacity: 0.15;
  pointer-events: none;
}

.mac-node-icon {
  width: 28px;
  height: 28px;
  background: rgba(0,0,0,0.03);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--theme-color);
}
.mac-node-icon :deep(svg) {
  width: 16px;
  height: 16px;
  stroke-width: 2px;
}

.mac-node-title {
  font-size: 13px;
  font-weight: 600;
  color: #1d1d1f;
  letter-spacing: -0.01em;
}

.vue-flow__node.selected .mac-node {
  box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.3), 0 8px 24px rgba(0,0,0,0.08);
  border-color: #007aff;
}

/* Handles */
.mac-handle {
  width: 10px;
  height: 10px;
  background: #fff;
  border: 2px solid #007aff;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
  bottom: -5px;
}
.mac-handle.top {
  top: -5px;
}

.vue-flow__edge-path {
  stroke: #86868b;
  stroke-width: 2.5;
}
.vue-flow__edge.selected .vue-flow__edge-path {
  stroke: #007aff;
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
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  z-index: 30;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
}

.panel-header {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.panel-header h4 { margin: 0; font-size: 13px; color: #1e293b; }
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
.empty-notif { padding: 20px; text-align: center; color: #64748b; font-size: 13px; }

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

.mac-toast {
  position: absolute;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 24px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-radius: 100px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1), 0 1px 3px rgba(0,0,0,0.05);
  border: 1px solid rgba(0,0,0,0.05);
  color: #1d1d1f;
  font-size: 14px;
  font-weight: 500;
}
.mac-toast.success {
  border: 1px solid rgba(52, 199, 89, 0.3);
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translate(-50%, -20px);
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

.mac-controls {
  background: rgba(255,255,255,0.85);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(0,0,0,0.06);
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.04);
  padding: 4px;
}
.vue-flow__controls-button {
  background: transparent;
  border: none;
  color: #1d1d1f;
  fill: #1d1d1f;
  border-radius: 8px;
}
.vue-flow__controls-button:hover {
  background: rgba(0,0,0,0.04);
}
</style>
