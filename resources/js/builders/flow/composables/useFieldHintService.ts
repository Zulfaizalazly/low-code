import { ref } from 'vue'
import type { Ref } from 'vue'

export interface PopoverState {
  visible: boolean
  anchorEl: HTMLElement | null
  nodeType: string
  fieldKey: string
  fieldLabel: string
  quickHint: string | null
  detailedHint: string | null
  followUpAnswer: string | null
  loading: boolean
  error: string | null
}

const INITIAL_STATE: PopoverState = {
  visible: false,
  anchorEl: null,
  nodeType: '',
  fieldKey: '',
  fieldLabel: '',
  quickHint: null,
  detailedHint: null,
  followUpAnswer: null,
  loading: false,
  error: null,
}

export default function useFieldHintService() {
  const cache = new Map<string, string>()
  const popover: Ref<PopoverState> = ref({ ...INITIAL_STATE })
  
  // Track last request for retry
  let lastRequest: { mode: string; userQuestion?: string } | null = null

  function getCacheKey(nodeType: string, fieldKey: string): string {
    return `${nodeType}::${fieldKey}`
  }

  function getCSRFToken(): string {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  }

  async function callAPI(nodeType: string, fieldKey: string, fieldLabel: string, mode: string, userQuestion?: string): Promise<string> {
    const response = await fetch('/api/studio/ai/field-hint', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCSRFToken(),
        'Accept': 'application/json',
      },
      body: JSON.stringify({ nodeType, fieldKey, fieldLabel, mode, userQuestion }),
    })

    if (response.status === 429) {
      throw new Error('Too many requests. Please wait a moment.')
    }

    if (!response.ok) {
      const data = await response.json().catch(() => ({}))
      throw new Error(data.message || 'Unable to load hint. Please try again.')
    }

    const data = await response.json()
    return data.hint || ''
  }

  async function openHint(anchorEl: HTMLElement, nodeType: string, fieldKey: string, fieldLabel: string): Promise<void> {
    // Close current if different field
    const cacheKey = getCacheKey(nodeType, fieldKey)
    
    // Reset state for new popover
    popover.value = {
      visible: true,
      anchorEl,
      nodeType,
      fieldKey,
      fieldLabel,
      quickHint: null,
      detailedHint: null,
      followUpAnswer: null,
      loading: true,
      error: null,
    }

    // Check cache
    if (cache.has(cacheKey)) {
      popover.value.quickHint = cache.get(cacheKey)!
      popover.value.loading = false
      return
    }

    // Fetch from API
    lastRequest = { mode: 'quick' }
    try {
      const hint = await callAPI(nodeType, fieldKey, fieldLabel, 'quick')
      cache.set(cacheKey, hint)
      popover.value.quickHint = hint
      popover.value.loading = false
    } catch (e: any) {
      popover.value.loading = false
      popover.value.error = e.message
    }
  }

  async function fetchDetailed(): Promise<void> {
    popover.value.loading = true
    popover.value.error = null
    lastRequest = { mode: 'detailed' }

    try {
      const hint = await callAPI(
        popover.value.nodeType,
        popover.value.fieldKey,
        popover.value.fieldLabel,
        'detailed'
      )
      popover.value.detailedHint = hint
      popover.value.loading = false
    } catch (e: any) {
      popover.value.loading = false
      popover.value.error = e.message
    }
  }

  async function askFollowUp(question: string): Promise<void> {
    popover.value.loading = true
    popover.value.error = null
    lastRequest = { mode: 'detailed', userQuestion: question }

    try {
      const hint = await callAPI(
        popover.value.nodeType,
        popover.value.fieldKey,
        popover.value.fieldLabel,
        'detailed',
        question
      )
      popover.value.followUpAnswer = hint
      popover.value.loading = false
    } catch (e: any) {
      popover.value.loading = false
      popover.value.error = e.message
    }
  }

  async function retry(): Promise<void> {
    if (!lastRequest) return
    
    if (lastRequest.mode === 'quick') {
      popover.value.loading = true
      popover.value.error = null
      try {
        const hint = await callAPI(
          popover.value.nodeType,
          popover.value.fieldKey,
          popover.value.fieldLabel,
          'quick'
        )
        const cacheKey = getCacheKey(popover.value.nodeType, popover.value.fieldKey)
        cache.set(cacheKey, hint)
        popover.value.quickHint = hint
        popover.value.loading = false
      } catch (e: any) {
        popover.value.loading = false
        popover.value.error = e.message
      }
    } else if (lastRequest.userQuestion) {
      await askFollowUp(lastRequest.userQuestion)
    } else {
      await fetchDetailed()
    }
  }

  function closePopover(): void {
    popover.value = { ...INITIAL_STATE }
  }

  function clearCache(): void {
    cache.clear()
  }

  return { popover, openHint, fetchDetailed, askFollowUp, retry, closePopover, clearCache }
}
