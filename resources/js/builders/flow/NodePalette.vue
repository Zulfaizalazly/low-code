<script setup>
/**
 * NodePalette — Draggable sidebar with categorized node types.
 * Drag a node type onto the canvas to create a new node.
 */
const categories = [
  {
    name: 'Entry',
    nodes: [
      { type: 'trigger', label: 'Trigger', icon: '⚡', desc: 'Flow entry point' },
    ],
  },
  {
    name: 'Actions',
    nodes: [
      { type: 'command', label: 'Command', icon: '⚙️', desc: 'Execute domain command' },
      { type: 'approval', label: 'Approval', icon: '✅', desc: 'Create approval task' },
      { type: 'notification', label: 'Notification', icon: '📧', desc: 'Send SMS/Email' },
      { type: 'document', label: 'Document', icon: '📄', desc: 'Generate document' },
      { type: 'gl_action', label: 'GL Entry', icon: '💰', desc: 'Post journal entry' },
    ],
  },
  {
    name: 'Logic',
    nodes: [
      { type: 'decision', label: 'Decision', icon: '🔀', desc: 'Branch by condition' },
      { type: 'formula', label: 'Formula', icon: '🔢', desc: 'Calculate expression' },
    ],
  },
  {
    name: 'System',
    nodes: [
      { type: 'end', label: 'End', icon: '🏁', desc: 'Flow termination' },
    ],
  },
]

function onDragStart(event, nodeType) {
  event.dataTransfer.setData('application/nodeType', nodeType)
  event.dataTransfer.effectAllowed = 'move'
}
</script>

<template>
  <aside class="node-palette">
    <div class="palette-header">
      <span class="palette-icon">🧩</span>
      <span>Node Palette</span>
    </div>

    <div v-for="category in categories" :key="category.name" class="category">
      <div class="category-label">{{ category.name }}</div>
      <div
        v-for="node in category.nodes"
        :key="node.type"
        class="palette-node"
        draggable="true"
        @dragstart="(e) => onDragStart(e, node.type)"
      >
        <span class="node-icon">{{ node.icon }}</span>
        <div class="node-info">
          <div class="node-label">{{ node.label }}</div>
          <div class="node-desc">{{ node.desc }}</div>
        </div>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.node-palette {
  width: 220px;
  background: rgba(15, 23, 42, 0.6);
  border-right: 1px solid rgba(255,255,255,0.05);
  padding: 16px 12px;
  overflow-y: auto;
  flex-shrink: 0;
  backdrop-filter: blur(12px);
}

.palette-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}

.palette-icon {
  font-size: 18px;
}

.category {
  margin-bottom: 16px;
}

.category-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: #64748b;
  margin-bottom: 8px;
  padding-left: 4px;
}

.palette-node {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 10px;
  cursor: grab;
  transition: all 0.15s;
  margin-bottom: 4px;
  border: 1px solid transparent;
}

.palette-node:hover {
  background: rgba(99, 102, 241, 0.08);
  border-color: rgba(99, 102, 241, 0.15);
}

.palette-node:active {
  cursor: grabbing;
  transform: scale(0.97);
}

.node-icon {
  font-size: 18px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.04);
  border-radius: 8px;
  flex-shrink: 0;
}

.node-info {
  min-width: 0;
}

.node-label {
  font-size: 12px;
  font-weight: 600;
  color: #cbd5e1;
}

.node-desc {
  font-size: 10px;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
