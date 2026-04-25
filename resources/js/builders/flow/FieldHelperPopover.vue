<script setup>
/**
 * FieldHelperPopover — Dark-glass popover for AI-generated field hints.
 *
 * Teleported to <body> to escape overflow clipping. Positions itself
 * relative to the anchor element, preferring below-right with auto-flip.
 */
import { ref, watch, onMounted, onBeforeUnmount, nextTick, computed } from 'vue'

const props = defineProps({
  popover: { type: Object, required: true },
})

const emit = defineEmits(['fetch-detailed', 'ask-follow-up', 'retry', 'close'])

const popoverEl = ref(null)
const followUpText = ref('')
const posStyle = ref({ top: '0px', left: '0px' })

const POPOVER_MAX_WIDTH = 320
const POPOVER_MARGIN = 8

function computePosition() {
  if (!props.popover.anchorEl) return

  const anchor = props.popover.anchorEl.getBoundingClientRect()
  const vw = window.innerWidth
  const vh = window.innerHeight

  // Estimate popover height (will refine after render)
  const popoverHeight = popoverEl.value?.offsetHeight || 200

  let top = anchor.bottom + POPOVER_MARGIN
  let left = anchor.left

  // Flip up if overflowing bottom
  if (top + popoverHeight > vh - POPOVER_MARGIN) {
    top = anchor.top - popoverHeight - POPOVER_MARGIN
  }

  // Clamp top to viewport
  if (top < POPOVER_MARGIN) {
    top = POPOVER_MARGIN
  }

  // Flip left if overflowing right
  if (left + POPOVER_MAX_WIDTH > vw - POPOVER_MARGIN) {
    left = vw - POPOVER_MAX_WIDTH - POPOVER_MARGIN
  }

  // Clamp left to viewport
  if (left < POPOVER_MARGIN) {
    left = POPOVER_MARGIN
  }

  posStyle.value = {
    top: `${top}px`,
    left: `${left}px`,
  }
}

// Reposition when popover becomes visible or content changes
watch(
  () => [props.popover.visible, props.popover.quickHint, props.popover.detailedHint, props.popover.followUpAnswer, props.popover.loading, props.popover.error],
  async () => {
    if (props.popover.visible) {
      await nextTick()
      computePosition()
    }
  },
  { immediate: true }
)

// Click outside handler
function onClickOutside(e) {
  if (!popoverEl.value) return
  if (!popoverEl.value.contains(e.target)) {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('mousedown', onClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', onClickOutside)
})

function sendFollowUp() {
  const q = followUpText.value.trim()
  if (!q) return
  emit('ask-follow-up', q)
  followUpText.value = ''
}
</script>

<template>
  <Teleport to="body">
    <transition name="hint-fade">
      <div
        v-if="popover.visible"
        ref="popoverEl"
        class="hint-popover"
        :style="posStyle"
      >
        <!-- Arrow caret -->
        <div class="hint-arrow"></div>

        <!-- Loading initial -->
        <div v-if="popover.loading && !popover.quickHint && !popover.error" class="hint-loading">
          <span class="hint-spinner"></span>
          <span class="hint-loading-text">Thinking…</span>
        </div>

        <!-- Quick hint -->
        <div v-if="popover.quickHint" class="hint-quick">
          <p class="hint-text">{{ popover.quickHint }}</p>
        </div>

        <!-- "Complete explanation" button -->
        <button
          v-if="popover.quickHint && !popover.detailedHint && !popover.loading"
          class="hint-detail-btn"
          @click="emit('fetch-detailed')"
        >
          Complete explanation →
        </button>

        <!-- Loading detailed -->
        <div v-if="popover.loading && popover.quickHint && !popover.detailedHint && !popover.error" class="hint-loading hint-loading-sub">
          <span class="hint-spinner"></span>
          <span class="hint-loading-text">Loading details…</span>
        </div>

        <!-- Detailed explanation -->
        <div v-if="popover.detailedHint" class="hint-detailed">
          <p class="hint-text hint-text-detailed">{{ popover.detailedHint }}</p>
        </div>

        <!-- Follow-up input (shown after detailed) -->
        <div v-if="popover.detailedHint && !popover.loading" class="hint-followup-form">
          <input
            v-model="followUpText"
            type="text"
            class="hint-followup-input"
            placeholder="Ask a follow-up…"
            maxlength="500"
            @keyup.enter="sendFollowUp"
          />
          <button class="hint-send-btn" @click="sendFollowUp" :disabled="!followUpText.trim()">
            Send
          </button>
        </div>

        <!-- Loading follow-up -->
        <div v-if="popover.loading && popover.detailedHint && !popover.followUpAnswer && !popover.error" class="hint-loading hint-loading-sub">
          <span class="hint-spinner"></span>
          <span class="hint-loading-text">Thinking…</span>
        </div>

        <!-- Follow-up answer -->
        <div v-if="popover.followUpAnswer" class="hint-followup-answer">
          <p class="hint-text">{{ popover.followUpAnswer }}</p>
        </div>

        <!-- Error state -->
        <div v-if="popover.error" class="hint-error">
          <p class="hint-error-text">{{ popover.error }}</p>
          <button class="hint-retry-btn" @click="emit('retry')">Retry</button>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<style scoped>
.hint-popover {
  position: fixed;
  z-index: 9999;
  max-width: 320px;
  min-width: 220px;
  background: rgba(15, 23, 42, 0.92);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  padding: 14px 16px;
  box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
  color: #e2e8f0;
  font-size: 13px;
  line-height: 1.55;
}

.hint-arrow {
  position: absolute;
  top: -6px;
  left: 16px;
  width: 12px;
  height: 12px;
  background: rgba(15, 23, 42, 0.92);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  transform: rotate(45deg);
}

/* Fade transition */
.hint-fade-enter-active,
.hint-fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.hint-fade-enter-from,
.hint-fade-leave-to {
  opacity: 0;
  transform: translateY(4px);
}

/* Loading */
.hint-loading {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 0;
}

.hint-loading-sub {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.hint-spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.15);
  border-top-color: #818cf8;
  border-radius: 50%;
  animation: hint-spin 0.7s linear infinite;
  flex-shrink: 0;
}

@keyframes hint-spin {
  to { transform: rotate(360deg); }
}

.hint-loading-text {
  font-size: 12px;
  color: #94a3b8;
}

/* Quick hint */
.hint-quick {
  margin: 0;
}

.hint-text {
  margin: 0;
  font-size: 13px;
  line-height: 1.55;
  color: #cbd5e1;
}

/* "Complete explanation" button */
.hint-detail-btn {
  display: inline-block;
  margin-top: 10px;
  padding: 0;
  background: none;
  border: none;
  color: #818cf8;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: color 0.15s ease;
}

.hint-detail-btn:hover {
  color: #a5b4fc;
}

/* Detailed explanation */
.hint-detailed {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.hint-text-detailed {
  font-size: 12px;
  color: #94a3b8;
  background: rgba(0, 0, 0, 0.15);
  padding: 10px 12px;
  border-radius: 8px;
  line-height: 1.6;
}

/* Follow-up form */
.hint-followup-form {
  display: flex;
  gap: 6px;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.hint-followup-input {
  flex: 1;
  padding: 7px 10px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  color: #e2e8f0;
  font-size: 12px;
  outline: none;
  transition: border-color 0.15s ease;
}

.hint-followup-input:focus {
  border-color: rgba(99, 102, 241, 0.4);
}

.hint-followup-input::placeholder {
  color: #475569;
}

.hint-send-btn {
  padding: 7px 12px;
  background: rgba(99, 102, 241, 0.2);
  border: 1px solid rgba(99, 102, 241, 0.3);
  border-radius: 8px;
  color: #a5b4fc;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.hint-send-btn:hover:not(:disabled) {
  background: rgba(99, 102, 241, 0.3);
}

.hint-send-btn:disabled {
  opacity: 0.4;
  cursor: default;
}

/* Follow-up answer */
.hint-followup-answer {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

/* Error state */
.hint-error {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 6px;
}

.hint-error-text {
  margin: 0;
  font-size: 12px;
  color: #fca5a5;
  flex: 1;
}

.hint-retry-btn {
  padding: 5px 12px;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 8px;
  color: #fca5a5;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.hint-retry-btn:hover {
  background: rgba(239, 68, 68, 0.2);
}
</style>
