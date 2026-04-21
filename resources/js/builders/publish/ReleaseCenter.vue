<script setup>
import { ref, onMounted } from 'vue'

const activeTab = ref('drafts')
const loading = ref(true)
const searchQuery = ref('')
const sortBy = ref('date') // 'date', 'name', 'risk'
const versions = ref({
  drafts: [],
  in_review: [],
  approved: [],
  published: [],
  archived: []
})

const rollbackLogs = ref([])

const summaryStats = computed(() => ({
  drafts: versions.value.drafts.length,
  pending_reviews: versions.value.in_review.length,
  published: versions.value.published.length,
  failed_simulations: 0 // TODO: fetch from simulation logs
}))

const filteredVersions = computed(() => {
  let list = versions.value[activeTab.value] || []
  
  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    list = list.filter(v => 
      v.feature?.name?.toLowerCase().includes(query) ||
      v.version_no?.toString().includes(query)
    )
  }
  
  // Sort
  if (sortBy.value === 'name') {
    list = [...list].sort((a, b) => 
      (a.feature?.name || '').localeCompare(b.feature?.name || '')
    )
  } else if (sortBy.value === 'date') {
    list = [...list].sort((a, b) => 
      new Date(b.updated_at) - new Date(a.updated_at)
    )
  }
  
  return list
})

async function fetchData() {
  loading.value = true
  try {
    const [vRes, hRes] = await Promise.all([
      fetch('/api/studio/versions'),
      fetch('/api/studio/versions/rollback-history')
    ])
    
    const vData = await vRes.json()
    const hData = await hRes.json()
    
    if (vData.success) versions.value = vData.data
    if (hData.success) rollbackLogs.value = hData.logs
  } catch (error) {
    console.error('Failed to fetch data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)

function viewReview(versionId) {
  window.location.href = `/studio/releases/${versionId}/review`
}

function continueDraft(version) {
  // Logic to redirect to Flow or Page builder
  const featureId = version.feature_id
  window.location.href = `/studio/features/${featureId}/workspace?version=${version.id}`
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleString()
}
</script>

<template>
  <div class="release-center">
    <header class="page-header">
      <div class="title-section">
        <h1>Release Center</h1>
        <p>Manage gobernance, approvals, and deployments for all features.</p>
      </div>
      <div class="actions">
        <button class="refresh-btn" @click="fetchData" :disabled="loading">
          <span :class="{ 'spinning': loading }">🔄</span> Refresh
        </button>
      </div>
    </header>

    <!-- Summary Cards -->
    <div class="summary-cards">
      <div class="summary-card">
        <div class="card-icon">📝</div>
        <div class="card-content">
          <div class="card-value">{{ summaryStats.drafts }}</div>
          <div class="card-label">Draft Features</div>
        </div>
      </div>
      <div class="summary-card highlight">
        <div class="card-icon">🕵️</div>
        <div class="card-content">
          <div class="card-value">{{ summaryStats.pending_reviews }}</div>
          <div class="card-label">Pending Reviews</div>
        </div>
      </div>
      <div class="summary-card">
        <div class="card-icon">🚀</div>
        <div class="card-content">
          <div class="card-value">{{ summaryStats.published }}</div>
          <div class="card-label">Published</div>
        </div>
      </div>
      <div class="summary-card">
        <div class="card-icon">⚠️</div>
        <div class="card-content">
          <div class="card-value">{{ summaryStats.failed_simulations }}</div>
          <div class="card-label">Failed Simulations</div>
        </div>
      </div>
    </div>

    <div class="dashboard-grid">
      <!-- Tabs Sidebar -->
      <aside class="sidebar-tabs">
        <button 
          v-for="tab in ['drafts', 'in_review', 'approved', 'published', 'archived', 'rollbacks']" 
          :key="tab"
          :class="['tab-btn', { active: activeTab === tab }]"
          @click="activeTab = tab"
        >
          <span class="tab-icon">{{ 
            tab === 'drafts' ? '📝' : 
            tab === 'in_review' ? '🕵️' : 
            tab === 'approved' ? '✅' : 
            tab === 'published' ? '🚀' : 
            tab === 'archived' ? '📦' : '↩️' 
          }}</span>
          <span class="tab-label">{{ tab.replace('_', ' ').toUpperCase() }}</span>
          <span class="tab-count" v-if="versions[tab]">{{ versions[tab].length }}</span>
        </button>
      </aside>

      <!-- Content Area -->
      <main class="content-view">
        <!-- Filters & Search -->
        <div class="content-toolbar">
          <div class="search-box">
            <span class="search-icon">🔍</span>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search features..." 
              class="search-input"
            />
          </div>
          <div class="sort-controls">
            <label>Sort by:</label>
            <select v-model="sortBy" class="sort-select">
              <option value="date">Date (Newest)</option>
              <option value="name">Name (A-Z)</option>
            </select>
          </div>
        </div>

        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Loading deployment data...</p>
        </div>

        <div v-else class="version-list">
          <!-- Versions List -->
          <template v-if="activeTab !== 'rollbacks'">
            <div v-if="filteredVersions.length === 0" class="empty-state">
              <p>{{ searchQuery ? 'No matching versions found.' : 'No versions in this state.' }}</p>
            </div>
            <div v-for="v in filteredVersions" :key="v.id" class="version-card glass">
              <div class="card-info">
                <div class="feature-name">{{ v.feature.name }}</div>
                <div class="version-tag">V{{ v.version_no }}</div>
                <div class="update-time">Last updated: {{ formatDate(v.updated_at) }}</div>
              </div>
              <div class="card-actions">
                <button v-if="activeTab === 'drafts'" @click="continueDraft(v)" class="primary-btn">Continue Design</button>
                <button v-if="activeTab === 'in_review'" @click="viewReview(v.id)" class="secondary-btn">Review Impact</button>
                <button v-if="activeTab === 'approved'" @click="viewReview(v.id)" class="primary-btn">Deploy to Production</button>
                <button v-if="activeTab === 'published'" @click="viewReview(v.id)" class="secondary-btn">Monitor & Rollback</button>
              </div>
            </div>
          </template>

          <!-- Rollback Logs List -->
          <template v-else>
            <div v-if="rollbackLogs.length === 0" class="empty-state">
              <p>No rollback events recorded.</p>
            </div>
            <div v-for="log in rollbackLogs" :key="log.id" class="log-card glass danger">
              <div class="log-info">
                <div class="feature-name">{{ log.feature_name }} V{{ log.version_no }}</div>
                <div class="log-reason">"{{ log.reason }}"</div>
                <div class="log-meta">By {{ log.user_name }} on {{ formatDate(log.rolled_back_at) }}</div>
              </div>
            </div>
          </template>
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
.release-center {
  padding: 32px; color: #1e293b; min-height: 100vh;
  background: linear-gradient(135deg, #f0f9ff 0%, #fef3c7 100%);
}

.page-header {
  display: flex; justify-content: space-between; align-items: flex-end;
  margin-bottom: 40px;
}
.page-header h1 { font-size: 32px; font-weight: 800; margin: 0; color: #0f172a; }
.page-header p { color: #475569; margin: 8px 0 0; }

.summary-cards {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 32px;
}

.summary-card {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 20px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.summary-card:hover {
  transform: translateY(-2px);
  border-color: rgba(99, 102, 241, 0.3);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.summary-card.highlight {
  border-color: rgba(99, 102, 241, 0.3);
  background: rgba(99, 102, 241, 0.05);
}

.card-icon {
  font-size: 32px;
  opacity: 0.8;
}

.card-content {
  flex: 1;
}

.card-value {
  font-size: 32px;
  font-weight: 800;
  color: #0f172a;
  line-height: 1;
  margin-bottom: 6px;
}

.card-label {
  font-size: 12px;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.refresh-btn {
  background: white; border: 1px solid rgba(0,0,0,0.1);
  color: #1e293b; padding: 10px 16px; border-radius: 12px; cursor: pointer;
  display: flex; align-items: center; gap: 8px; transition: all 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.refresh-btn:hover { background: #f8fafc; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
.spinning { animation: spin 1s linear infinite; display: inline-block; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.dashboard-grid {
  display: grid; grid-template-columns: 280px 1fr; gap: 32px;
}

.sidebar-tabs {
  display: flex; flex-direction: column; gap: 8px;
}
.tab-btn {
  display: flex; align-items: center; gap: 12px; padding: 14px 18px;
  background: transparent; border: 1px solid transparent; border-radius: 16px;
  color: #475569; cursor: pointer; transition: all 0.2s; text-align: left;
}
.tab-btn:hover { background: rgba(0,0,0,0.03); color: #1e293b; }
.tab-btn.active { 
  background: white; border: 1px solid rgba(99, 102, 241, 0.2);
  color: #6366f1;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.tab-icon { font-size: 18px; }
.tab-label { flex: 1; font-size: 13px; font-weight: 700; letter-spacing: 0.05em; }
.tab-count {
  background: rgba(99,102,241,0.1); padding: 2px 8px; border-radius: 20px;
  font-size: 11px; color: #4338ca; font-weight: 700;
}

.content-view {
  min-height: 500px;
}

.content-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 16px;
}

.search-box {
  flex: 1;
  max-width: 400px;
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 14px;
  font-size: 16px;
  opacity: 0.5;
}

.search-input {
  width: 100%;
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  padding: 10px 14px 10px 42px;
  color: #1e293b;
  font-size: 14px;
  outline: none;
  transition: all 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.search-input:focus {
  border-color: #6366f1;
  background: white;
  box-shadow: 0 2px 8px rgba(99,102,241,0.15);
}

.sort-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.sort-controls label {
  font-size: 13px;
  color: #475569;
  font-weight: 600;
}

.sort-select {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 10px;
  padding: 8px 12px;
  color: #1e293b;
  font-size: 13px;
  outline: none;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.glass {
  background: white; 
  border: 1px solid rgba(0, 0, 0, 0.08); 
  border-radius: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.version-card {
  padding: 24px; margin-bottom: 16px; display: flex; justify-content: space-between;
  align-items: center; transition: transform 0.2s;
}
.version-card:hover { transform: translateY(-2px); border-color: rgba(99, 102, 241, 0.3); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

.card-info .feature-name { font-size: 18px; font-weight: 700; color: #0f172a; }
.card-info .version-tag { 
  display: inline-block; margin-top: 4px; padding: 2px 10px; 
  background: #e0e7ff; border-radius: 6px; font-size: 12px; color: #4338ca;
}
.card-info .update-time { margin-top: 12px; font-size: 12px; color: #64748b; }

.card-actions { display: flex; gap: 12px; }
.primary-btn {
  background: #6366f1; color: white; padding: 10px 20px; border-radius: 12px;
  border: none; font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.primary-btn:hover { background: #4f46e5; box-shadow: 0 0 20px rgba(99, 102, 241, 0.3); }

.secondary-btn {
  background: white; color: #475569; padding: 10px 20px; 
  border-radius: 12px; border: 1px solid rgba(0,0,0,0.1); font-weight: 600; 
  cursor: pointer; transition: all 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.secondary-btn:hover { background: #f8fafc; color: #1e293b; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }

.log-card { padding: 20px; margin-bottom: 12px; }
.log-card.danger { border-left: 4px solid #ef4444; }
.log-reason { margin: 8px 0; color: #ef4444; font-style: italic; font-size: 14px; }
.log-meta { font-size: 12px; color: #64748b; }

.empty-state {
  text-align: center; padding: 80px 0; color: #94a3b8;
}
.loading-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 100px 0;
}
.spinner {
  width: 40px; height: 40px; border: 3px solid rgba(99, 102, 241, 0.1);
  border-top-color: #6366f1; border-radius: 50%; animation: spin 1s infinite linear;
  margin-bottom: 16px;
}
</style>
