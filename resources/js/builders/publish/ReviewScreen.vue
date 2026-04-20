<script setup>
import { ref, onMounted, computed } from 'vue'
import SimulationModal from './SimulationModal.vue'

const props = defineProps({
  versionId: { type: [String, Number], required: true }
})

const loading = ref(true)
const processing = ref(false)
const version = ref(null)
const report = ref(null)
const validations = ref(null)
const showSimModal = ref(false)
const selectedFlowKey = ref(null)

const comments = ref('')

async function fetchData() {
  loading.value = true
  try {
    const [vRes, rRes, valRes] = await Promise.all([
      fetch(`/api/studio/versions/${props.versionId}`),
      fetch(`/api/studio/versions/${props.versionId}/impact-analysis`),
      fetch(`/api/studio/versions/${props.versionId}/validations`)
    ])
    
    const vData = await vRes.json()
    const rData = await rRes.json()
    const valData = await valRes.json()
    
    version.value = vData.version
    if (rData.success) report.value = rData.report
    if (valData.success) validations.value = valData
  } catch (error) {
    console.error('Failed to fetch data:', error)
  } finally {
    loading.value = false
  }
}

async function runAnalysis() {
  processing.value = true
  try {
    const res = await fetch(`/api/studio/versions/${props.versionId}/impact-analysis`, { method: 'POST' })
    const data = await res.json()
    if (data.success) report.value = data.report
  } catch (error) {
    alert('Analysis failed: ' + error.message)
  } finally {
    processing.value = false
  }
}

async function handleAction(action) {
  if (action === 'reject' && !comments.value) {
    alert('Please provide comments for rejection.')
    return
  }

  processing.value = true
  try {
    const res = await fetch(`/api/studio/versions/${props.versionId}/${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comments: comments.value, reason: comments.value })
    })
    const data = await res.json()
    if (data.success) {
      alert(`Successfully ${action}ed.`)
      window.location.href = '/studio/releases'
    } else {
      alert(`${action} failed: ` + data.message)
    }
  } catch (error) {
    alert('Action failed: ' + error.message)
  } finally {
    processing.value = false
  }
}

function startSimulation(flowKey) {
  selectedFlowKey.value = flowKey
  showSimModal.value = true
}

onMounted(fetchData)

const riskColor = computed(() => {
  if (!report.value) return '#64748b'
  const map = { low: '#10b981', medium: '#f59e0b', high: '#ef4444', critical: '#7f1d1d' }
  return map[report.value.risk_level] || '#64748b'
})
</script>

<template>
  <div class="review-screen">
    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
    </div>

    <template v-else-if="version">
      <header class="review-header glass">
        <div class="breadcrumb">
          <a href="/studio/releases">Release Center</a> / Reviewing V{{ version.version_no }}
        </div>
        <div class="header-main">
          <div class="feature-info">
            <h1>{{ version.feature.name }}</h1>
            <div class="status-badge" :data-status="version.status">{{ version.status.toUpperCase() }}</div>
          </div>
          <div class="header-risk" v-if="report">
            <span class="risk-label">RISK LEVEL:</span>
            <span class="risk-value" :style="{ color: riskColor }">{{ report.risk_level.toUpperCase() }}</span>
          </div>
        </div>
      </header>

      <div class="review-grid">
        <!-- Left: Impact Report -->
        <section class="impact-section glass">
          <div class="section-header">
            <h2>Impact Analysis Report</h2>
            <button @click="runAnalysis" :disabled="processing" class="refresh-analysis">
              Re-run Analysis
            </button>
          </div>

          <div v-if="!report" class="empty-report">
            <p>No analysis performed yet.</p>
            <button @click="runAnalysis" class="primary-btn">Analyze Now</button>
          </div>

          <div v-else class="report-content">
            <!-- affected data -->
            <div class="report-group">
              <h3>Target Landscape</h3>
              <div class="metric-grid">
                <div class="metric">
                  <span class="m-val">{{ report.affected_branches.count }}</span>
                  <span class="m-label">Branches</span>
                </div>
                <div class="metric">
                  <span class="m-val">{{ report.affected_roles.length }}</span>
                  <span class="m-label">Roles Affected</span>
                </div>
                <div class="metric">
                  <span class="m-val">{{ report.data_impact.count }}</span>
                  <span class="m-label">Entities</span>
                </div>
              </div>

              <!-- Detailed Branch List -->
              <div class="detail-list">
                <h4>Affected Branches</h4>
                <div v-if="report.affected_branches.scope === 'all_branches'" class="detail-note">
                  🌐 All branches (platform-wide deployment)
                </div>
                <div v-else class="branch-chips">
                  <span v-for="branch in report.affected_branches.branches" :key="branch" class="chip">
                    {{ branch }}
                  </span>
                </div>
              </div>

              <!-- Detailed Role List -->
              <div class="detail-list">
                <h4>Affected Roles & Permissions</h4>
                <div class="role-list">
                  <div v-for="role in report.affected_roles" :key="role.role" class="role-item">
                    <div class="role-name">
                      <span class="role-badge">{{ role.role }}</span>
                      <span v-if="role.is_new" class="new-badge">NEW</span>
                    </div>
                    <div class="role-meta">
                      <span class="role-permission">{{ role.permission }}</span>
                      <span class="role-source">via {{ role.source }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- UI Impact -->
              <div class="detail-list">
                <h4>UI Components</h4>
                <div class="ui-list">
                  <div v-for="page in report.ui_impact.pages" :key="page.page_key" class="ui-item">
                    <span class="ui-icon">📄</span>
                    <div class="ui-info">
                      <div class="ui-name">{{ page.page_name }}</div>
                      <div class="ui-stats">{{ page.steps }} steps, {{ page.fields }} fields</div>
                    </div>
                  </div>
                  <div v-for="menu in report.ui_impact.menu_items" :key="menu.route_key" class="ui-item">
                    <span class="ui-icon">📌</span>
                    <div class="ui-info">
                      <div class="ui-name">{{ menu.label }}</div>
                      <div class="ui-stats">Menu: {{ menu.parent || 'Root' }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- side effects -->
            <div class="report-group">
              <h3>Side Effects & Outputs</h3>
              <ul class="effect-list">
                <li v-for="doc in report.automation_outputs.documents" :key="doc.node_key">
                  📄 Document: <strong>{{ doc.document_type }}</strong>
                  <span :class="['verify-tag', doc.risk]">
                    {{ doc.template_exists ? 'Template Valid' : 'Template Missing' }}
                  </span>
                </li>
                <li v-for="gl in report.automation_outputs.gl_entries" :key="gl.node_key">
                  💰 GL Entry: <strong>{{ gl.description }}</strong>
                </li>
              </ul>
            </div>
            
            <!-- Validation Results -->
            <div v-if="validations" class="report-group">
              <h3>Publish Gate Validations ({{ validations.summary.passed }}/{{ validations.summary.total }} Passed)</h3>
              <div class="validation-summary">
                <div class="val-stat passed">✓ {{ validations.summary.passed }} Passed</div>
                <div class="val-stat failed">✗ {{ validations.summary.failed }} Failed</div>
                <div class="val-stat warning">⚠ {{ validations.summary.warning }} Warnings</div>
                <div class="val-stat skipped">○ {{ validations.summary.skipped }} Skipped</div>
              </div>
              <div class="validation-list">
                <div 
                  v-for="val in validations.validations" 
                  :key="val.check_key"
                  :class="['validation-item', val.status]"
                >
                  <span class="val-icon">
                    {{ val.status === 'passed' ? '✓' : val.status === 'failed' ? '✗' : val.status === 'warning' ? '⚠' : '○' }}
                  </span>
                  <div class="val-content">
                    <div class="val-key">{{ val.check_key.replace(/_/g, ' ').toUpperCase() }}</div>
                    <div class="val-message">{{ val.message }}</div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- reports -->
            <div class="report-group">
              <h3>Reporting Impact</h3>
              <div v-for="rep in report.data_impact.affected_reports" :key="rep.name" class="report-item">
                📊 {{ rep.name }} (Risk: {{ rep.risk }})
              </div>
            </div>
          </div>
        </section>

        <!-- Right: Simulation & Actions -->
        <aside class="actions-section">
          <!-- Simulation -->
          <div class="sim-card glass">
            <h3>Workflow Simulation</h3>
            <p>Dry-run the automation flows to verify logic and variables.</p>
            <div class="flow-list">
              <div v-for="flow in version.flows" :key="flow.id" class="flow-item">
                <span class="flow-name">⚡ {{ flow.name }}</span>
                <button @click="startSimulation(flow.key)" class="sim-btn">Simulate</button>
              </div>
            </div>
          </div>

          <!-- Approval Form -->
          <div class="decision-card glass">
            <h3>Final Governance</h3>
            <textarea v-model="comments" placeholder="Reviewer comments or rollback reason..." class="comments-box"></textarea>
            
            <div class="action-buttons">
              <template v-if="version.status === 'in_review'">
                <button @click="handleAction('approve')" :disabled="processing" class="approve-btn">Approve Version</button>
                <button @click="handleAction('reject')" :disabled="processing" class="reject-btn">Reject to Draft</button>
              </template>
              
              <template v-if="version.status === 'approved'">
                <button @click="handleAction('publish')" :disabled="processing" class="publish-btn">🚀 Deploy to Production</button>
              </template>

              <template v-if="version.status === 'published'">
                <button @click="handleAction('rollback')" :disabled="processing" class="rollback-btn">↩️ Rollback to Previous</button>
              </template>
            </div>
          </div>
        </aside>
      </div>

      <SimulationModal 
        v-if="showSimModal"
        :show="showSimModal"
        :version-id="versionId"
        :flow-key="selectedFlowKey"
        @close="showSimModal = false"
      />
    </template>
  </div>
</template>

<style scoped>
.review-screen {
  padding: 32px; color: #f1f5f9; min-height: 100vh;
  background: #0f172a;
}

.glass {
  background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 24px;
}

.review-header { padding: 24px 32px; margin-bottom: 24px; }
.breadcrumb { font-size: 12px; color: #64748b; margin-bottom: 12px; }
.breadcrumb a { color: inherit; text-decoration: none; }
.breadcrumb a:hover { color: #818cf8; }

.header-main { display: flex; justify-content: space-between; align-items: center; }
.feature-info { display: flex; align-items: center; gap: 16px; }
.feature-info h1 { margin: 0; font-size: 24px; font-weight: 800; }
.status-badge { 
  padding: 4px 12px; border-radius: 99px; font-size: 11px; font-weight: 700;
  background: #334155; color: #94a3b8;
}
.status-badge[data-status="in_review"] { background: #1e3a8a; color: #93c5fd; }
.status-badge[data-status="approved"] { background: #064e3b; color: #6ee7b7; }
.status-badge[data-status="published"] { background: #4c1d95; color: #ddd6fe; }

.header-risk { text-align: right; }
.risk-label { font-size: 11px; font-weight: 700; color: #64748b; margin-right: 8px; }
.risk-value { font-size: 20px; font-weight: 900; }

.review-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; }

.impact-section { padding: 32px; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
.section-header h2 { margin: 0; font-size: 18px; font-weight: 700; }
.refresh-analysis {
  background: transparent; border: 1px solid #475569; color: #94a3b8;
  padding: 8px 16px; border-radius: 10px; font-size: 12px; cursor: pointer;
}

.report-group { margin-bottom: 32px; }
.report-group h3 { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 16px; }

.metric-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.metric { 
  background: rgba(0,0,0,0.2); padding: 16px; border-radius: 16px;
  display: flex; flex-direction: column; align-items: center;
}
.m-val { font-size: 24px; font-weight: 800; color: white; }
.m-label { font-size: 11px; color: #64748b; margin-top: 4px; }

.effect-list { list-style: none; padding: 0; margin: 0; }
.effect-list li { 
  padding: 12px 16px; background: rgba(0,0,0,0.1); border-radius: 12px;
  margin-bottom: 8px; font-size: 13px; display: flex; justify-content: space-between; align-items: center;
}
.verify-tag { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
.verify-tag.low { background: #064e3b; color: #6ee7b7; }
.verify-tag.high { background: #7f1d1d; color: #fca5a5; }

.validation-summary {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
}

.val-stat {
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 6px;
}

.val-stat.passed { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.val-stat.failed { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.val-stat.warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.val-stat.skipped { background: rgba(100, 116, 139, 0.1); color: #64748b; }

.validation-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.validation-item {
  display: flex;
  gap: 12px;
  padding: 12px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 10px;
  border-left: 3px solid transparent;
}

.validation-item.passed { border-left-color: #10b981; }
.validation-item.failed { border-left-color: #ef4444; }
.validation-item.warning { border-left-color: #f59e0b; }
.validation-item.skipped { border-left-color: #64748b; }

.val-icon {
  font-size: 16px;
  font-weight: 700;
}

.validation-item.passed .val-icon { color: #10b981; }
.validation-item.failed .val-icon { color: #ef4444; }
.validation-item.warning .val-icon { color: #f59e0b; }
.validation-item.skipped .val-icon { color: #64748b; }

.val-content {
  flex: 1;
}

.val-key {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  margin-bottom: 4px;
  letter-spacing: 0.05em;
}

.val-message {
  font-size: 13px;
  color: #cbd5e1;
}

.detail-list {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-list h4 {
  font-size: 13px;
  font-weight: 700;
  color: #e2e8f0;
  margin: 0 0 12px 0;
}

.detail-note {
  padding: 12px;
  background: rgba(99, 102, 241, 0.1);
  border: 1px solid rgba(99, 102, 241, 0.2);
  border-radius: 8px;
  color: #a5b4fc;
  font-size: 13px;
}

.branch-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.chip {
  padding: 6px 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  font-size: 12px;
  color: #cbd5e1;
}

.role-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.role-item {
  padding: 12px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.role-name {
  display: flex;
  align-items: center;
  gap: 8px;
}

.role-badge {
  padding: 4px 10px;
  background: rgba(99, 102, 241, 0.2);
  border: 1px solid rgba(99, 102, 241, 0.3);
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #a5b4fc;
  font-family: monospace;
}

.new-badge {
  padding: 2px 6px;
  background: rgba(16, 185, 129, 0.2);
  border: 1px solid rgba(16, 185, 129, 0.3);
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  color: #6ee7b7;
}

.role-meta {
  display: flex;
  gap: 12px;
  font-size: 12px;
}

.role-permission {
  color: #94a3b8;
  font-weight: 600;
}

.role-source {
  color: #64748b;
}

.ui-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.ui-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 8px;
}

.ui-icon {
  font-size: 18px;
}

.ui-info {
  flex: 1;
}

.ui-name {
  font-size: 13px;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 2px;
}

.ui-stats {
  font-size: 11px;
  color: #64748b;
}

.actions-section { display: flex; flex-direction: column; gap: 24px; }
.sim-card, .decision-card { padding: 24px; }
.sim-card h3, .decision-card h3 { font-size: 16px; margin: 0 0 8px; }
.sim-card p { font-size: 13px; color: #94a3b8; margin-bottom: 20px; }

.flow-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 12px; background: rgba(255,255,255,0.03); border-radius: 12px; margin-bottom: 8px;
}
.flow-name { font-size: 13px; font-weight: 600; }
.sim-btn {
  background: #334155; border: none; color: white; font-size: 11px; 
  font-weight: 700; padding: 6px 12px; border-radius: 8px; cursor: pointer;
}

.comments-box {
  width: 100%; min-height: 100px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 12px; color: #cbd5e1; padding: 12px; font-size: 13px; margin: 16px 0;
}

.action-buttons { display: flex; flex-direction: column; gap: 12px; }
.approve-btn, .publish-btn {
  background: #6366f1; color: white; padding: 12px; border-radius: 12px; border: none; font-weight: 700; cursor: pointer;
}
.reject-btn, .rollback-btn {
  background: transparent; border: 1px solid #ef4444; color: #ef4444; padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer;
}

.approve-btn:hover, .publish-btn:hover { box-shadow: 0 0 20px rgba(99, 102, 241, 0.4); }
.reject-btn:hover, .rollback-btn:hover { background: rgba(239, 68, 68, 0.1); }

.loading-overlay { display: flex; align-items: center; justify-content: center; height: 80vh; }
.spinner { width: 40px; height: 40px; border: 3px solid rgba(255,255,255,0.1); border-top-color: #6366f1; border-radius: 50%; animation: spin 1s infinite linear; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
