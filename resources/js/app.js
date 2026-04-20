import './bootstrap';
import { createApp } from 'vue';
import FlowCanvas from './builders/flow/FlowCanvas.vue';

const flowElement = document.getElementById('flow-canvas');
if (flowElement) {
    const app = createApp(FlowCanvas, {
        flowId: flowElement.dataset.flowId,
        initialNodes: JSON.parse(flowElement.dataset.nodes || '[]'),
        initialEdges: JSON.parse(flowElement.dataset.edges || '[]'),
    });
    app.mount('#flow-canvas');

    // Bridge for the "Update Definition" button in Livewire
    document.getElementById('save-flow-btn')?.addEventListener('click', () => {
        if (window.saveFlowToLivewire) {
            window.saveFlowToLivewire();
        }
    });
}
