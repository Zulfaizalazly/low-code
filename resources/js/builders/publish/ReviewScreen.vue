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
    const res = await fetch(`/api/studio/versions/${props.versionId}/impact-analysis`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
      }
    })
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

  if (action === 'rollback' && !comments.value) {
    alert('Please provide a reason for rollback.')
    return
  }

  if (action === 'retire' && !comments.value) {
    alert('Please provide a reason for retirement (e.g. policy change, regulatory update).')
    return
  }

  if (action === 'retire') {
    if (!confirm('⚠️ This will permanently decommission this feature. All versions will be archived. Data is preserved for audit. Continue?')) {
      return
    }
  }

  processing.value = true
  try {
    const res = await fetch(`/api/studio/versions/${props.versionId}/${action}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
      },
      body: JSON.stringify({ comments: comments.value, reason: comments.value })
    })
    const data = await res.json()
    if (data.success) {
      alert(`Successfully ${action}ed.`)
      if (action === 'retire') {
        window.location.href = '/studio'
      } else {
        window.location.href = '/studio/releases'
      }
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
  if (!report.value) return '#86868b'
  const map = { 
    low: '#34c759', 
    medium: '#ff9500', 
    high: '#ff3b30', 
    critical: '#8e24aa' 
  }
  return map[report.value.risk_level] || '#86868b'
})

const icons = {
  branch: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>',
  role: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
  entity: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>',
  page: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
  menu: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10H3M21 6H3M21 14H3M21 18H3"></path></svg>',
  gl: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><path d="M12 12h.01"></path><path d="M17 12h.01"></path><path d="M7 12h.01"></path></svg>',
  zap: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
  check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
  x: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
  warn: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
  info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
}

</script>

<template>
  <div class="review-screen">
    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
    </div>

    <template v-else-if="version">
      <header class="review-header glass">
        <div class="breadcrumb">
          <a href="/studio/releases">Release Center</a> / <span class="current">Reviewing V{{ version.version_no }}</span>
        </div>
        <div class="header-main">
          <div class="feature-info">
            <div class="icon-box" v-html="icons.zap"></div>
            <div>
              <h1>{{ version.feature.name }}</h1>
              <div class="status-badge" :data-status="version.status">{{ version.status.replace('_', ' ').toUpperCase() }}</div>
            </div>
          </div>
          <div class="header-risk" v-if="report">
            <span class="risk-label">RISK ASSESSMENT</span>
            <div class="risk-pill" :style="{ color: riskColor, backgroundColor: riskColor + '15', borderColor: riskColor + '30' }">
              <span class="pulse" :style="{ backgroundColor: riskColor }"></span>
              {{ report.risk_level.toUpperCase() }}
            </div>
          </div>
        </div>
      </header>

      <div class="review-grid">
        <!-- Left: Impact Report -->
        <section class="impact-section glass">
          <div class="section-header">
            <div class="title-with-icon">
              <span class="header-icon" v-html="icons.info"></span>
              <h2>Impact Analysis Report</h2>
            </div>
            <button @click="runAnalysis" :disabled="processing" class="refresh-analysis">
              <svg :class="{'animate-spin': processing}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
              Refresh Analysis
            </button>
          </div>

          <div v-if="!report" class="empty-report">
            <div class="empty-icon" v-html="icons.info"></div>
            <p>No impact analysis has been performed for this version yet.</p>
            <button @click="runAnalysis" class="primary-btn">Analyze Impact Now</button>
          </div>

          <div v-else class="report-content">
            <!-- Target Landscape -->
            <div class="report-group">
              <h3>Target Landscape</h3>
              <div class="metric-grid">
                <div class="metric">
                  <div class="m-icon" v-html="icons.branch"></div>
                  <span class="m-val">{{ report.affected_branches.count }}</span>
                  <span class="m-label">Branches</span>
                </div>
                <div class="metric">
                  <div class="m-icon" v-html="icons.role"></div>
                  <span class="m-val">{{ report.affected_roles.length }}</span>
                  <span class="m-label">Roles</span>
                </div>
                <div class="metric">
                  <div class="m-icon" v-html="icons.entity"></div>
                  <span class="m-val">{{ report.data_impact.count }}</span>
                  <span class="m-label">Entities</span>
                </div>
              </div>

              <!-- Detailed Branch List -->
              <div class="detail-list">
                <h4>Deployment Scope</h4>
                <div v-if="report.affected_branches.scope === 'all_branches'" class="detail-note">
                  <span class="note-icon" v-html="icons.branch"></span>
                  All branches (Platform-wide deployment)
                </div>
                <div v-else class="branch-chips">
                  <span v-for="branch in report.affected_branches.branches" :key="branch" class="chip">
                    {{ branch }}
                  </span>
                </div>
              </div>

              <!-- Detailed Role List -->
              <div class="detail-list">
                <h4>Access Control Impact</h4>
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
                <h4>Interface Modifications</h4>
                <div class="ui-list">
                  <div v-for="page in report.ui_impact.pages" :key="page.page_key" class="ui-item">
                    <span class="ui-icon-box" v-html="icons.page"></span>
                    <div class="ui-info">
                      <div class="ui-name">{{ page.page_name }}</div>
                      <div class="ui-stats">{{ page.steps }} steps • {{ page.fields }} fields</div>
                    </div>
                  </div>
                  <div v-for="menu in report.ui_impact.menu_items" :key="menu.route_key" class="ui-item">
                    <span class="ui-icon-box" v-html="icons.menu"></span>
                    <div class="ui-info">
                      <div class="ui-name">{{ menu.label }}</div>
                      <div class="ui-stats">Menu Navigation: {{ menu.parent || 'Root' }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Automation Side Effects -->
            <div class="report-group">
              <h3>System Side Effects</h3>
              <div class="effect-list">
                <div v-for="doc in report.automation_outputs.documents" :key="doc.node_key" class="effect-item">
                  <div class="effect-main">
                    <span class="effect-icon" v-html="icons.page"></span>
                    <span class="effect-text">Generate Document: <strong>{{ doc.document_type }}</strong></span>
                  </div>
                  <span :class="['verify-tag', doc.risk]">
                    {{ doc.template_exists ? 'Template Verified' : 'Template Missing' }}
                  </span>
                </div>
                <div v-for="gl in report.automation_outputs.gl_entries" :key="gl.node_key" class="effect-item">
                  <div class="effect-main">
                    <span class="effect-icon" v-html="icons.gl"></span>
                    <span class="effect-text">GL Journal: <strong>{{ gl.description }}</strong></span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Validation Results -->
            <div v-if="validations" class="report-group">
              <h3>Publish Gate Validations</h3>
              <div class="validation-summary">
                <div class="val-stat passed">
                  <span class="v-icon-small" v-html="icons.check"></span>
                  {{ validations.summary.passed }} Passed
                </div>
                <div class="val-stat failed" v-if="validations.summary.failed > 0">
                  <span class="v-icon-small" v-html="icons.x"></span>
                  {{ validations.summary.failed }} Failed
                </div>
                <div class="val-stat warning" v-if="validations.summary.warning > 0">
                  <span class="v-icon-small" v-html="icons.warn"></span>
                  {{ validations.summary.warning }} Warnings
                </div>
              </div>
              <div class="validation-list">
                <div 
                  v-for="val in validations.validations" 
                  :key="val.check_key"
                  :class="['validation-item', val.status]"
                >
                  <span class="val-icon" v-html="val.status === 'passed' ? icons.check : (val.status === 'failed' ? icons.x : icons.warn)"></span>
                  <div class="val-content">
                    <div class="val-key">{{ val.check_key.replace(/_/g, ' ').toUpperCase() }}</div>
                    <div class="val-message">{{ val.message }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Right: Simulation & Actions -->
        <aside class="actions-section">
          <!-- Simulation -->
          <div class="sim-card glass">
            <h3>Workflow Simulation</h3>
            <p>Dry-run the automation flows to verify logic and variable mapping before deployment.</p>
            <div class="flow-list">
              <div v-for="flow in version.flows" :key="flow.id" class="flow-item">
                <div class="flow-info">
                  <span class="flow-icon" v-html="icons.zap"></span>
                  <span class="flow-name">{{ flow.name }}</span>
                </div>
                <button @click="startSimulation(flow.key)" class="sim-btn">Run</button>
              </div>
            </div>
          </div>

          <!-- Approval Form -->
          <div class="decision-card glass">
            <h3>Governance Decision</h3>
            <div class="input-group">
              <label>Reviewer Comments</label>
              <textarea v-model="comments" :placeholder="version.status === 'published' ? 'Provide reason for rollback or retirement (required)...' : 'Add your feedback or reasoning for the action taken...'" class="comments-box"></textarea>
            </div>
            
            <div class="action-buttons">
              <template v-if="version.status === 'in_review'">
                <button @click="handleAction('approve')" :disabled="processing" class="approve-btn">
                  Approve for Release
                </button>
                <button @click="handleAction('reject')" :disabled="processing" class="reject-btn">
                  Reject to Draft
                </button>
              </template>
              
              <template v-if="version.status === 'approved'">
                <button @click="handleAction('publish')" :disabled="processing" class="publish-btn">
                  🚀 Deploy to Production
                </button>
              </template>

              <template v-if="version.status === 'published'">
                <button @click="handleAction('rollback')" :disabled="processing" class="rollback-btn">
                  Rollback to Previous
                </button>
                <button @click="handleAction('retire')" :disabled="processing" class="retire-btn">
                  🗄️ Retire Feature
                </button>
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
  padding: 0; 
  color: #1d1d1f; 
  min-height: 100vh;
}

.glass {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 24px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
}

.review-header { 
  padding: 32px; 
  margin-bottom: 32px; 
  border-radius: 0 0 24px 24px;
  border-top: none;
}

.breadcrumb { 
  font-size: 13px; 
  color: #86868b; 
  margin-bottom: 16px; 
  font-weight: 500;
}

.breadcrumb a { 
  color: inherit; 
  text-decoration: none; 
}

.breadcrumb a:hover { 
  color: #007aff; 
}

.breadcrumb .current {
  color: #1d1d1f;
  font-weight: 600;
}

.header-main { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
}

.feature-info { 
  display: flex; 
  align-items: center; 
  gap: 20px; 
}

.icon-box {
  width: 52px;
  height: 52px;
  background: #007aff;
  color: white;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 16px rgba(0, 122, 255, 0.2);
}

.icon-box svg {
  width: 28px;
  height: 28px;
}

.feature-info h1 { 
  margin: 0 0 4px 0; 
  font-size: 28px; 
  font-weight: 700; 
  letter-spacing: -0.02em;
}

.status-badge { 
  display: inline-block;
  padding: 4px 10px; 
  border-radius: 6px; 
  font-size: 11px; 
  font-weight: 700;
  letter-spacing: 0.02em;
  background: #f5f5f7; 
  color: #86868b;
  border: 1px solid rgba(0,0,0,0.05);
}

.status-badge[data-status="in_review"] { background: #eef2ff; color: #4f46e5; border-color: #c7d2fe; }
.status-badge[data-status="approved"] { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
.status-badge[data-status="published"] { background: #faf5ff; color: #9333ea; border-color: #e9d5ff; }

.header-risk { 
  text-align: right; 
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 8px;
}

.risk-label { 
  font-size: 10px; 
  font-weight: 700; 
  color: #86868b; 
  letter-spacing: 0.05em;
}

.risk-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 100px;
  font-size: 15px;
  font-weight: 800;
  border: 1px solid transparent;
}

.pulse {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  position: relative;
}

.pulse::after {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  background: inherit;
  opacity: 0.4;
  animation: pulse-ring 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse-ring {
  0% { transform: scale(1); opacity: 0.4; }
  100% { transform: scale(2.5); opacity: 0; }
}

.review-grid { 
  display: grid; 
  grid-template-columns: 1fr 360px; 
  gap: 32px; 
  padding: 0 0 40px 0;
}

.impact-section { padding: 40px; }

.section-header { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  margin-bottom: 40px; 
}

.title-with-icon {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-icon {
  color: #007aff;
  width: 24px;
  height: 24px;
}

.section-header h2 { 
  margin: 0; 
  font-size: 20px; 
  font-weight: 700; 
  letter-spacing: -0.01em;
}

.refresh-analysis {
  display: flex;
  align-items: center;
  gap: 8px;
  background: white; 
  border: 1px solid rgba(0,0,0,0.1); 
  color: #1d1d1f;
  padding: 10px 18px; 
  border-radius: 12px; 
  font-size: 13px; 
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.refresh-analysis:hover {
  background: #f5f5f7;
  border-color: rgba(0,0,0,0.15);
}

.report-group { 
  margin-bottom: 48px; 
}

.report-group h3 { 
  font-size: 11px; 
  font-weight: 700; 
  text-transform: uppercase; 
  color: #86868b; 
  margin-bottom: 20px; 
  letter-spacing: 0.05em;
}

.metric-grid { 
  display: grid; 
  grid-template-columns: repeat(3, 1fr); 
  gap: 20px; 
}

.metric { 
  background: #f5f5f7; 
  padding: 24px; 
  border-radius: 20px;
  display: flex; 
  flex-direction: column; 
  align-items: center;
  border: 1px solid rgba(0,0,0,0.03);
  transition: transform 0.2s;
}

.metric:hover {
  transform: translateY(-2px);
  background: #efeff4;
}

.m-icon {
  color: #86868b;
  width: 20px;
  height: 20px;
  margin-bottom: 12px;
}

.m-val { 
  font-size: 32px; 
  font-weight: 700; 
  color: #1d1d1f; 
  line-height: 1;
}

.m-label { 
  font-size: 12px; 
  font-weight: 500;
  color: #86868b; 
  margin-top: 8px; 
}

.detail-list {
  margin-top: 32px;
  padding: 24px;
  background: rgba(0,0,0,0.02);
  border-radius: 20px;
}

.detail-list h4 {
  font-size: 14px;
  font-weight: 600;
  color: #1d1d1f;
  margin: 0 0 16px 0;
}

.detail-note {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: #eef2ff;
  border: 1px solid #c7d2fe;
  border-radius: 12px;
  color: #4338ca;
  font-size: 14px;
  font-weight: 500;
}

.note-icon {
  width: 18px;
  height: 18px;
}

.branch-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.chip {
  padding: 8px 14px;
  background: white;
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  color: #1d1d1f;
  box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}

.role-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.role-item {
  padding: 16px;
  background: white;
  border-radius: 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid rgba(0,0,0,0.05);
}

.role-badge {
  padding: 5px 12px;
  background: #f5f5f7;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #1d1d1f;
  font-family: 'SF Mono', monospace;
}

.new-badge {
  padding: 3px 8px;
  background: #dcfce7;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 700;
  color: #166534;
  margin-left: 10px;
}

.role-meta {
  display: flex;
  gap: 16px;
  font-size: 13px;
}

.role-permission {
  color: #1d1d1f;
  font-weight: 600;
}

.role-source {
  color: #86868b;
}

.ui-list {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.ui-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: white;
  border-radius: 16px;
  border: 1px solid rgba(0,0,0,0.05);
}

.ui-icon-box {
  width: 40px;
  height: 40px;
  background: #f5f5f7;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #86868b;
}

.ui-icon-box svg {
  width: 20px;
  height: 20px;
}

.ui-name {
  font-size: 14px;
  font-weight: 600;
  color: #1d1d1f;
}

.ui-stats {
  font-size: 12px;
  color: #86868b;
  margin-top: 2px;
}

.effect-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.effect-item { 
  padding: 16px 20px; 
  background: white; 
  border-radius: 16px;
  border: 1px solid rgba(0,0,0,0.05);
  display: flex; 
  justify-content: space-between; 
  align-items: center;
}

.effect-main {
  display: flex;
  align-items: center;
  gap: 14px;
}

.effect-icon {
  color: #007aff;
  width: 18px;
  height: 18px;
}

.effect-text {
  font-size: 14px;
}

.verify-tag { 
  font-size: 11px; 
  font-weight: 700; 
  padding: 4px 10px; 
  border-radius: 6px; 
}

.verify-tag.low { background: #dcfce7; color: #166534; }
.verify-tag.high { background: #fee2e2; color: #991b1b; }

.validation-summary {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
}

.val-stat {
  padding: 10px 20px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.v-icon-small {
  width: 14px;
  height: 14px;
}

.val-stat.passed { background: #f0fdf4; color: #16a34a; }
.val-stat.failed { background: #fef2f2; color: #dc2626; }
.val-stat.warning { background: #fffbeb; color: #d97706; }

.validation-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.validation-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: white;
  border-radius: 16px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-left-width: 4px;
}

.validation-item.passed { border-left-color: #34c759; }
.validation-item.failed { border-left-color: #ff3b30; }
.validation-item.warning { border-left-color: #ff9500; }

.val-icon {
  width: 20px;
  height: 20px;
  margin-top: 2px;
}

.validation-item.passed .val-icon { color: #34c759; }
.validation-item.failed .val-icon { color: #ff3b30; }
.validation-item.warning .val-icon { color: #ff9500; }

.val-content { flex: 1; }

.val-key {
  font-size: 11px;
  font-weight: 700;
  color: #86868b;
  margin-bottom: 4px;
  letter-spacing: 0.03em;
}

.val-message {
  font-size: 14px;
  color: #1d1d1f;
  line-height: 1.4;
}

.actions-section { 
  display: flex; 
  flex-direction: column; 
  gap: 32px; 
}

.sim-card, .decision-card { 
  padding: 32px; 
}

.sim-card h3, .decision-card h3 { 
  font-size: 18px; 
  font-weight: 700;
  margin: 0 0 12px; 
}

.sim-card p { 
  font-size: 14px; 
  color: #86868b; 
  margin-bottom: 24px; 
  line-height: 1.5;
}

.flow-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.flow-item {
  display: flex; 
  justify-content: space-between; 
  align-items: center;
  padding: 16px; 
  background: #f5f5f7; 
  border-radius: 16px;
  border: 1px solid rgba(0,0,0,0.03);
}

.flow-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.flow-icon {
  color: #007aff;
  width: 16px;
  height: 16px;
}

.flow-name { 
  font-size: 14px; 
  font-weight: 600; 
}

.sim-btn {
  background: white; 
  border: 1px solid rgba(0,0,0,0.1); 
  color: #1d1d1f; 
  font-size: 12px; 
  font-weight: 700; 
  padding: 8px 16px; 
  border-radius: 10px; 
  cursor: pointer;
  transition: all 0.2s;
}

.sim-btn:hover {
  background: #f5f5f7;
  transform: scale(1.05);
}

.input-group label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: #86868b;
  margin-bottom: 8px;
  text-transform: uppercase;
}

.comments-box {
  width: 100%; 
  min-height: 120px; 
  background: #f5f5f7; 
  border: 1px solid rgba(0,0,0,0.05);
  border-radius: 16px; 
  color: #1d1d1f; 
  padding: 16px; 
  font-size: 14px; 
  margin-bottom: 24px;
  outline: none;
  transition: border-color 0.2s;
  resize: vertical;
}

.comments-box:focus {
  border-color: #007aff;
  background: white;
}

.action-buttons { 
  display: flex; 
  flex-direction: column; 
  gap: 14px; 
}

.approve-btn, .publish-btn {
  background: #007aff; 
  color: white; 
  padding: 16px; 
  border-radius: 14px; 
  border: none; 
  font-weight: 700; 
  font-size: 15px;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 12px rgba(0, 122, 255, 0.2);
}

.approve-btn:hover, .publish-btn:hover { 
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 122, 255, 0.3);
  background: #0071e3;
}

.reject-btn, .rollback-btn {
  background: transparent; 
  border: 1px solid #ff3b30; 
  color: #ff3b30; 
  padding: 14px; 
  border-radius: 14px; 
  font-weight: 700; 
  font-size: 15px;
  cursor: pointer;
  transition: all 0.2s;
}

.reject-btn:hover, .rollback-btn:hover { 
  background: #fff5f5;
  border-color: #ff3b30;
}

.retire-btn {
  background: transparent;
  border: 1px solid #86868b;
  color: #86868b;
  padding: 14px;
  border-radius: 14px;
  font-weight: 700;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.2s;
}

.retire-btn:hover {
  background: #f5f5f7;
  border-color: #1d1d1f;
  color: #1d1d1f;
}

.approve-btn:active, .publish-btn:active, .reject-btn:active {
  transform: scale(0.98);
}

.loading-overlay { 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  height: 80vh; 
}

.spinner { 
  width: 44px; 
  height: 44px; 
  border: 3px solid rgba(0,0,0,0.05); 
  border-top-color: #007aff; 
  border-radius: 50%; 
  animation: spin 1s cubic-bezier(0.4, 0, 0.2, 1) infinite; 
}

.empty-report {
  text-align: center;
  padding: 60px 20px;
}

.empty-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 20px;
  color: #d1d1d6;
}

.empty-report p {
  color: #86868b;
  margin-bottom: 24px;
}

.primary-btn {
  background: #007aff;
  color: white;
  padding: 12px 24px;
  border-radius: 12px;
  border: none;
  font-weight: 600;
  cursor: pointer;
}

@keyframes spin { to { transform: rotate(360deg); } }

</style>
