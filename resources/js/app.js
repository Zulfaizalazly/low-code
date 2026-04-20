import './bootstrap';
import { createApp } from 'vue';
import FlowCanvas from './builders/flow/FlowCanvas.vue';
import PageBuilder from './builders/page/PageBuilder.vue';
import ReleaseCenter from './builders/publish/ReleaseCenter.vue';
import ReviewScreen from './builders/publish/ReviewScreen.vue';

// ─── Flow Builder (Vue Island) ───
const flowElement = document.getElementById('flow-canvas');
if (flowElement) {
    const app = createApp(FlowCanvas, {
        flowId: flowElement.dataset.flowId,
        versionId: flowElement.dataset.versionId,
        flowKey: flowElement.dataset.flowKey,
        initialNodes: JSON.parse(flowElement.dataset.nodes || '[]'),
        initialEdges: JSON.parse(flowElement.dataset.edges || '[]'),
        commands: JSON.parse(flowElement.dataset.commands || '[]'),
    });
    app.mount('#flow-canvas');

    document.getElementById('save-flow-btn')?.addEventListener('click', () => {
        if (window.saveFlowToLivewire) window.saveFlowToLivewire();
    });
}

// ─── Page Builder (Vue Island) ───
const pageElement = document.getElementById('page-builder');
if (pageElement) {
    const app = createApp(PageBuilder, {
        pageId: pageElement.dataset.pageId,
        versionId: pageElement.dataset.versionId,
        initialSteps: JSON.parse(pageElement.dataset.steps || '[]'),
        initialEntities: JSON.parse(pageElement.dataset.entities || '{}'),
    });
    app.mount('#page-builder');

    document.getElementById('save-page-btn')?.addEventListener('click', () => {
        if (window.savePageToLivewire) window.savePageToLivewire();
    });
}

// ─── Release Center ───
const releaseElement = document.getElementById('release-center-mount');
if (releaseElement) {
    const app = createApp(ReleaseCenter);
    app.mount('#release-center-mount');
}

// ─── Review Screen ───
const reviewElement = document.getElementById('review-screen-mount');
if (reviewElement) {
    const app = createApp(ReviewScreen, {
        versionId: reviewElement.children[0].getAttribute('version-id')
    });
    app.mount('#review-screen-mount');
}
