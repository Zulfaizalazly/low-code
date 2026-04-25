/**
 * usePreflightChecker — Scans flow nodes for config completeness.
 *
 * Uses the shared `getConfigFields` utility to determine required fields
 * per node type, then checks each field for emptiness.
 */

import { ref } from 'vue'
import type { Ref } from 'vue'
import { getConfigFields, type ConfigFieldDef } from './useConfigFields'

export interface PreflightNodeResult {
  nodeId: string
  nodeKey: string
  label: string
  nodeType: string
  passed: boolean
  missingFields: ConfigFieldDef[]
}

export interface PreflightResult {
  totalScanned: number
  totalPassed: number
  totalFailed: number
  nodeResults: PreflightNodeResult[]
}

/**
 * Read a potentially nested config value using dot-notation.
 * e.g. "credentials.api_key" → config.credentials?.api_key
 */
function getNestedValue(config: Record<string, any>, key: string): any {
  const parts = key.split('.')
  let current: any = config
  for (const part of parts) {
    if (current == null) return undefined
    current = current[part]
  }
  return current
}

/**
 * Determine whether a config field value is "empty" based on its type.
 */
function isFieldEmpty(value: any, fieldType: string): boolean {
  if (fieldType === 'number') {
    return value === undefined || value === null
  }
  if (fieldType === 'json') {
    if (value === undefined || value === null) return true
    if (typeof value === 'object' && Object.keys(value).length === 0) return true
    return false
  }
  // string / text / textarea / select — treat as string-like
  return value === undefined || value === null || value === ''
}

/**
 * Check a single node for config completeness.
 * Exported for direct testing.
 */
export function checkNode(node: any, commands: any[]): PreflightNodeResult {
  const nodeType: string = node.data?.nodeType ?? ''
  const config: Record<string, any> = node.data?.config ?? {}

  const fields = getConfigFields(nodeType, config, commands)

  // Filter out dividers — they are visual separators, not real fields
  const requiredFields = fields.filter((f) => f.type !== 'divider')

  const missingFields: ConfigFieldDef[] = []
  for (const field of requiredFields) {
    const value = getNestedValue(config, field.key)
    if (isFieldEmpty(value, field.type)) {
      missingFields.push(field)
    }
  }

  return {
    nodeId: node.id ?? '',
    nodeKey: node.data?.nodeKey ?? node.id ?? '',
    label: node.data?.label ?? '',
    nodeType,
    passed: missingFields.length === 0,
    missingFields,
  }
}

/**
 * Composable that provides reactive preflight scanning state and controls.
 */
export default function usePreflightChecker() {
  const isScanning: Ref<boolean> = ref(false)
  const scanProgress: Ref<number> = ref(0)
  const currentNode: Ref<{ label: string; nodeType: string } | null> = ref(null)
  const completedNodes: Ref<PreflightNodeResult[]> = ref([])
  const results: Ref<PreflightResult | null> = ref(null)
  const cancelled: Ref<boolean> = ref(false)

  async function runScan(
    nodes: any[],
    commands: any[],
    delayMs: number = 300,
  ): Promise<PreflightResult> {
    // Reset all state
    isScanning.value = true
    scanProgress.value = 0
    currentNode.value = null
    completedNodes.value = []
    results.value = null
    cancelled.value = false

    for (const node of nodes) {
      // Skip nodes without a nodeType — don't count them
      if (!node.data?.nodeType) {
        continue
      }

      currentNode.value = {
        label: node.data.label,
        nodeType: node.data.nodeType,
      }

      // Animated delay per node
      await new Promise((r) => setTimeout(r, delayMs))

      // Check for cancellation after the delay
      if (cancelled.value) {
        break
      }

      const result = checkNode(node, commands)
      completedNodes.value.push(result)
      scanProgress.value++
    }

    // Compute summary from completed nodes
    const nodeResults = completedNodes.value
    const totalScanned = nodeResults.length
    const totalPassed = nodeResults.filter((r) => r.passed).length
    const totalFailed = nodeResults.filter((r) => !r.passed).length

    const summary: PreflightResult = {
      totalScanned,
      totalPassed,
      totalFailed,
      nodeResults,
    }

    results.value = summary
    isScanning.value = false
    currentNode.value = null

    return summary
  }

  function cancelScan() {
    cancelled.value = true
  }

  return {
    isScanning,
    scanProgress,
    currentNode,
    completedNodes,
    results,
    cancelled,
    runScan,
    cancelScan,
  }
}
