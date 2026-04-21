<script setup>
/**
 * NodePalette — Draggable sidebar with categorized node types.
 * Drag a node type onto the canvas to create a new node.
 */

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

const categories = [
  {
    name: 'Entry',
    nodes: [
      { type: 'trigger', label: 'Trigger', icon: icons.trigger, desc: 'Flow entry point' },
    ],
  },
  {
    name: 'Actions',
    nodes: [
      { type: 'command', label: 'Command', icon: icons.command, desc: 'Execute domain rule' },
      { type: 'approval', label: 'Approval', icon: icons.approval, desc: 'Human intervention' },
      { type: 'notification', label: 'Notification', icon: icons.notification, desc: 'Send SMS/Email' },
      { type: 'document', label: 'Document', icon: icons.document, desc: 'Generate PDF/Word' },
      { type: 'gl_action', label: 'GL Entry', icon: icons.gl_action, desc: 'Ledger journal' },
    ],
  },
  {
    name: 'Logic',
    nodes: [
      { type: 'decision', label: 'Decision', icon: icons.decision, desc: 'Branch by condition' },
      { type: 'formula', label: 'Formula', icon: icons.formula, desc: 'Calculate expression' },
    ],
  },
  {
    name: 'System',
    nodes: [
      { type: 'end', label: 'End', icon: icons.end, desc: 'Flow termination' },
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
      <svg class="palette-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
      <span>Components</span>
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
        <span class="node-icon" v-html="node.icon"></span>
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
  width: 250px;
  background: rgba(255, 255, 255, 0.85);
  border-right: 1px solid rgba(0, 0, 0, 0.06);
  padding: 20px 16px;
  overflow-y: auto;
  flex-shrink: 0;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  z-index: 10;
}

.palette-header {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 15px;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  letter-spacing: -0.01em;
}

.palette-icon {
  width: 20px;
  height: 20px;
  color: #86868b;
}

.category {
  margin-bottom: 24px;
}

.category-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #a1a1a6;
  margin-bottom: 10px;
  padding-left: 4px;
}

.palette-node {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 12px;
  cursor: grab;
  transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
  margin-bottom: 6px;
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.04);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.palette-node:hover {
  background: #f5f5f7;
  border-color: rgba(0, 0, 0, 0.08);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}

.palette-node:active {
  cursor: grabbing;
  transform: scale(0.98);
}

.node-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fcfcfc;
  border: 1px solid rgba(0,0,0,0.06);
  border-radius: 8px;
  flex-shrink: 0;
  color: #1d1d1f;
}

.node-icon :deep(svg) {
  width: 16px;
  height: 16px;
  stroke: #1d1d1f;
}

.node-info {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.node-label {
  font-size: 13px;
  font-weight: 500;
  color: #1d1d1f;
  letter-spacing: -0.01em;
}

.node-desc {
  font-size: 11px;
  color: #86868b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
