import './bootstrap';
import { createApp } from 'vue';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import FlowCanvas from './builders/flow/FlowCanvas.vue';
import PageBuilder from './builders/page/PageBuilder.vue';
import ReleaseCenter from './builders/publish/ReleaseCenter.vue';
import ReviewScreen from './builders/publish/ReviewScreen.vue';

// Toast configuration
const toastOptions = {
    position: 'top-right',
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: 'button',
    icon: true,
    rtl: false
};

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
    app.use(Toast, toastOptions);
    app.mount('#flow-canvas');

    document.getElementById('save-flow-btn')?.addEventListener('click', async () => {
        const btn = document.getElementById('save-flow-btn');
        const spinner = document.getElementById('save-btn-spinner');
        const text = document.getElementById('save-btn-text');

        if (!window.saveFlowToLivewire) return;

        try {
            // Start Loading
            btn.disabled = true;
            spinner.classList.remove('hidden');
            text.innerText = 'Updating...';

            await window.saveFlowToLivewire();
            
            // Success State (Reset shortly after)
            text.innerText = 'Updated!';
            setTimeout(() => {
                text.innerText = 'Update Definition';
                btn.disabled = false;
                spinner.classList.add('hidden');
            }, 1500);

        } catch (error) {
            console.error('Save failed:', error);
            text.innerText = 'Error!';
            btn.classList.add('bg-red-600');
            setTimeout(() => {
                text.innerText = 'Update Definition';
                btn.disabled = false;
                spinner.classList.add('hidden');
                btn.classList.remove('bg-red-600');
            }, 3000);
        }
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
    app.use(Toast, toastOptions);
    app.mount('#page-builder');

    document.getElementById('save-page-btn')?.addEventListener('click', () => {
        if (window.savePageToLivewire) window.savePageToLivewire();
    });
}

// ─── Release Center ───
const releaseElement = document.getElementById('release-center-mount');
if (releaseElement) {
    const app = createApp(ReleaseCenter);
    app.use(Toast, toastOptions);
    app.mount('#release-center-mount');
}

// ─── Review Screen ───
const reviewElement = document.getElementById('review-screen-mount');
if (reviewElement) {
    const app = createApp(ReviewScreen, {
        versionId: reviewElement.children[0].getAttribute('version-id')
    });
    app.use(Toast, toastOptions);
    app.mount('#review-screen-mount');
}
