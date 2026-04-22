<script setup>
import { ref, computed } from 'vue'
import RefinementModal from './RefinementModal.vue'

const props = defineProps({
  show: Boolean,
  definition: Object,
  aspects: Array,
  options: Object,
  iterationCount: Number,
})

const emit = defineEmits(['close', 'publish', 'manual-override'])

const showRefinement = ref(false)

const complianceScore = computed(() => props.definition?._validation?.score || 100)
const scoreColor = computed(() => {
  if (complianceScore.value >= 90) return '#10b981' // Success
  if (complianceScore.value >= 70) return '#f59e0b' // Warning
  return '#ef4444' // Danger
})

function onRefine() {
  showRefinement.value = true
}

function closeRefinement() {
  showRefinement.value = false
}

function handleRefinementApplied(newResult) {
  // Update the definition with refined one
  // This is handled by the parent listening to 'ui-refined' event
  showRefinement.value = false
}
</script>

<template>
  <Transition name="fade">
    <div v-if="show" class="apple-modal-overlay" @click.self="$emit('close')">
      <Transition name="slide-up">
        <div v-if="show" class="apple-modal-card">
          <!-- Header -->
          <div class="apple-modal-header">
            <div class="header-titles">
              <div class="magic-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                  <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                  <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                  <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
              </div>
              <div class="titles">
                <h2>Automation Intelligence</h2>
                <p>Generated UI Specification</p>
              </div>
            </div>
            <button @click="$emit('close')" class="apple-close-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
          </div>

          <!-- Body -->
          <div class="apple-modal-body">
            <div class="compliance-banner" :class="{ warning: complianceScore < 90 }">
              <div class="banner-icon">
                 <svg v-if="complianceScore >= 90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                 <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
              </div>
              <div class="banner-text">
                <span class="banner-title">Design Compliance</span>
                <span class="banner-desc">Validated against Human Interface Guidelines</span>
              </div>
              <div class="banner-score" :style="{ color: scoreColor }">{{ complianceScore }}%</div>
            </div>

            <div v-if="complianceScore < 90" class="apple-warnings">
              <ul>
                <li v-for="v in definition._validation?.violations" :key="v.target">
                  <strong>{{ v.target }}</strong>: {{ v.message }}
                </li>
              </ul>
            </div>

            <div class="apple-code-block">
              <div class="code-header">
                <div class="code-dots"><i class="red"/><i class="yellow"/><i class="green"/></div>
                <span>PageDefinition.json</span>
                <div class="code-spacer"></div>
              </div>
              <div class="code-content">
                <pre><code>{{ JSON.stringify(definition, null, 2) }}</code></pre>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="apple-modal-footer">
            <button @click="$emit('manual-override')" class="apple-btn outline">Manual Override</button>
            <div class="footer-actions">
              <button @click="onRefine" class="apple-btn magic-outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                Refine with AI
              </button>
              <button @click="$emit('publish')" class="apple-btn primary">Accept & Publish</button>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>

  <!-- Refinement Modal Nesting -->
  <RefinementModal
    :show="showRefinement"
    :definition="definition"
    :aspects="aspects"
    :options="options"
    :iteration-count="iterationCount"
    @close="closeRefinement"
    @applied="handleRefinementApplied"
  />
</template>

<style scoped>
/* Translucent background overlay */
.apple-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

/* Beautiful frosted glass modal */
.apple-modal-card {
  width: 100%;
  max-width: 860px;
  max-height: 90vh;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(40px) saturate(200%);
  -webkit-backdrop-filter: blur(40px) saturate(200%);
  border-radius: 20px;
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Inter", sans-serif;
  color: #1d1d1f;
}

/* Header */
.apple-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  background: rgba(255, 255, 255, 0.6);
}

.header-titles {
  display: flex;
  align-items: center;
  gap: 16px;
}

.magic-icon {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, rgba(175, 82, 222, 0.15) 0%, rgba(88, 86, 214, 0.15) 100%);
  color: #af52de;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: inset 0 0 0 1px rgba(175, 82, 222, 0.1);
}

.titles h2 {
  margin: 0;
  font-size: 19px;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: #1d1d1f;
}

.titles p {
  margin: 2px 0 0 0;
  font-size: 13px;
  color: #86868b;
}

.apple-close-btn {
  background: #f2f2f7;
  color: #86868b;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}
.apple-close-btn:hover { background: #e5e5ea; color: #1d1d1f; }

/* Body Area */
.apple-modal-body {
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
  background: #fbfbfd;
}

/* Compliance Banner */
.compliance-banner {
  display: flex;
  align-items: center;
  background: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.05);
  padding: 16px;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
}

.banner-icon {
  width: 40px;
  height: 40px;
  border-radius: 20px;
  background: rgba(52, 199, 89, 0.1);
  color: #34c759;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
}

.compliance-banner.warning .banner-icon {
  background: rgba(255, 149, 0, 0.1);
  color: #ff9500;
}

.banner-text {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.banner-title {
  font-size: 15px;
  font-weight: 600;
  color: #1d1d1f;
}

.banner-desc {
  font-size: 13px;
  color: #86868b;
  margin-top: 2px;
}

.banner-score {
  font-size: 22px;
  font-weight: 700;
  letter-spacing: -0.03em;
  padding-left: 16px;
}

/* Warnings */
.apple-warnings {
  background: #fff0f0;
  border: 1px solid #ffcaca;
  padding: 16px;
  border-radius: 12px;
  color: #c0392b;
  font-size: 13px;
}
.apple-warnings ul {
  margin: 0;
  padding-left: 20px;
}
.apple-warnings li { margin-bottom: 4px; }

/* Code Viewer (macOS terminal style) */
.apple-code-block {
  background: #1e1e1e;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255,255,255,0.1);
  flex: 1;
  display: flex;
  flex-direction: column;
}

.code-header {
  background: #2d2d2d;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.code-dots {
  display: flex;
  gap: 6px;
  width: 60px;
}

.code-dots i {
  width: 10px;
  height: 10px;
  border-radius: 5px;
  background: #ff5f56;
}
.code-dots i.yellow { background: #ffbd2e; }
.code-dots i.green { background: #27c93f; }

.code-header span {
  flex: 1;
  text-align: center;
  color: #a0a0a0;
  font-size: 12px;
  font-weight: 500;
  font-family: -apple-system, BlinkMacSystemFont, sans-serif;
}

.code-spacer {
  width: 60px;
}

.code-content {
  padding: 16px;
  max-height: 400px;
  overflow-y: auto;
}

.code-content pre {
  margin: 0;
  font-family: 'SF Mono', ui-monospace, Menlo, Monaco, Consolas, monospace;
  font-size: 13px;
  line-height: 1.5;
  color: #d4d4d4;
}

/* Footer Section */
.apple-modal-footer {
  padding: 20px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.8);
  border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.footer-actions {
  display: flex;
  gap: 12px;
}

.apple-btn {
  padding: 12px 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", sans-serif;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
  border: none;
}

.apple-btn.outline {
  background: transparent;
  color: #1d1d1f;
  border: 1px solid #d1d1d6;
}
.apple-btn.outline:hover { background: #f2f2f7; }

.apple-btn.magic-outline {
  background: rgba(175, 82, 222, 0.08);
  color: #af52de;
}
.apple-btn.magic-outline:hover { background: rgba(175, 82, 222, 0.15); }

.apple-btn.primary {
  background: #007aff;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 122, 255, 0.3);
}
.apple-btn.primary:hover { background: #0066d6; transform: scale(1.02); }
.apple-btn.primary:active { transform: scale(0.98); }

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-up-enter-active, .slide-up-leave-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translateY(20px) scale(0.98); }
</style>
