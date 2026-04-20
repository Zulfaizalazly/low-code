<script setup>
import { ref, onMounted } from 'vue'
import { VueFlow, useVueFlow } from '@vue-flow/core'
import { Background } from '@vue-flow/background'
import { Controls } from '@vue-flow/controls'

const props = defineProps({
  flowId: String,
  initialNodes: Array,
  initialEdges: Array
})

const { onPaneReady, onNodeDragStop, onConnect, addEdges, toObject } = useVueFlow()

const nodes = ref([])
const edges = ref([])

onMounted(() => {
  // Map Laravel models to Vue Flow nodes
  nodes.value = props.initialNodes.map(node => ({
    id: node.node_key,
    type: node.node_type === 'trigger' ? 'input' : (node.node_type === 'end' ? 'output' : 'default'),
    position: { x: node.position_x, y: node.position_y },
    data: { label: node.label, config: node.config },
  }))

  edges.value = props.initialEdges.map(edge => ({
    id: `e-${edge.id}`,
    source: edge.source_node_key, // We'll need to make sure we have the key/id mapping
    target: edge.target_node_key,
    animated: true,
  }))
})

const saveFlow = () => {
  const flowObject = toObject()
  window.dispatchEvent(new CustomEvent('vue-flow-save', {
    detail: {
      nodes: flowObject.nodes,
      edges: flowObject.edges
    }
  }))
}

// Expose saveFlow to the window for the Livewire button
window.saveFlowToLivewire = saveFlow
</script>

<template>
  <div style="height: 100%; width: 100%;">
    <VueFlow
      v-model:nodes="nodes"
      v-model:edges="edges"
      :fit-view-on-init="true"
      class="arrahnu-flow-canvas"
    >
      <Background pattern-color="#334155" :gap="20" />
      <Controls />
    </VueFlow>
  </div>
</template>

<style>
@import '@vue-flow/core/dist/style.css';
@import '@vue-flow/core/dist/theme-default.css';
@import '@vue-flow/controls/dist/style.css';

.arrahnu-flow-canvas {
  background-color: transparent;
}

.vue-flow__node {
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  border-radius: 12px;
  padding: 10px;
  backdrop-filter: blur(8px);
}

.vue-flow__edge-path {
  stroke: #6366f1;
  stroke-width: 2.5;
}
</style>
