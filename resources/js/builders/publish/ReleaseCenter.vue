<script setup>
import { ref, onMounted, computed } from 'vue'

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

const icons = {
  drafts: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',
  in_review: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
  approved: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
  published: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
  archived: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>',
  rollbacks: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>'
}


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
    const fetchOptions = {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    }

    const [vRes, hRes] = await Promise.all([
      fetch('/api/studio/versions', fetchOptions),
      fetch('/api/studio/versions/rollback-history', fetchOptions)
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
  <div class="h-full bg-transparent p-4 sm:p-8 max-w-[1300px] mx-auto font-sans">
    <!-- Header -->
    <header class="flex justify-between items-end mb-10 mt-2">
      <div>
        <h1 class="text-[32px] font-bold tracking-tight text-[#1d1d1f]">Release Center</h1>
        <p class="text-[15px] text-[#86868b] mt-1.5 font-medium">Manage deployment lifecycles, approvals, and live features.</p>
      </div>
      <div>
        <button @click="fetchData" :disabled="loading" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-black/5 shadow-sm rounded-xl text-[14px] font-semibold text-[#1d1d1f] hover:bg-[#f5f5f7] transition-all active:scale-95 disabled:opacity-50">
          <svg :class="{'animate-spin': loading}" class="w-4 h-4 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          Refresh
        </button>
      </div>
    </header>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-10">
      <!-- Card 1 -->
      <div class="bg-white border border-black/[0.04] p-5 rounded-[20px] shadow-sm flex items-center gap-4 transition-transform hover:-translate-y-0.5 duration-300">
        <div class="w-12 h-12 rounded-[14px] bg-blue-50 flex items-center justify-center shrink-0">
          <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
        </div>
        <div>
          <div class="text-[28px] font-bold text-[#1d1d1f] leading-none tracking-tight">{{ summaryStats.drafts }}</div>
          <div class="text-[12px] font-semibold text-[#86868b] tracking-wide uppercase mt-1">Drafts</div>
        </div>
      </div>
      <!-- Card 2 -->
      <div class="bg-white border border-black/[0.04] p-5 rounded-[20px] shadow-sm flex items-center gap-4 transition-transform hover:-translate-y-0.5 duration-300 relative overflow-hidden">
        <div class="absolute inset-0 bg-amber-50/30"></div>
        <div class="relative w-12 h-12 rounded-[14px] bg-amber-100/60 flex items-center justify-center shrink-0">
          <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
        </div>
        <div class="relative">
          <div class="text-[28px] font-bold text-[#1d1d1f] leading-none tracking-tight">{{ summaryStats.pending_reviews }}</div>
          <div class="text-[12px] font-semibold text-[#86868b] tracking-wide uppercase mt-1">In Review</div>
        </div>
      </div>
      <!-- Card 3 -->
      <div class="bg-white border border-black/[0.04] p-5 rounded-[20px] shadow-sm flex items-center gap-4 transition-transform hover:-translate-y-0.5 duration-300">
        <div class="w-12 h-12 rounded-[14px] bg-emerald-50 flex items-center justify-center shrink-0">
          <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" /></svg>
        </div>
        <div>
          <div class="text-[28px] font-bold text-[#1d1d1f] leading-none tracking-tight">{{ summaryStats.published }}</div>
          <div class="text-[12px] font-semibold text-[#86868b] tracking-wide uppercase mt-1">Published</div>
        </div>
      </div>
      <!-- Card 4 -->
      <div class="bg-white border border-black/[0.04] p-5 rounded-[20px] shadow-sm flex items-center gap-4 transition-transform hover:-translate-y-0.5 duration-300">
        <div class="w-12 h-12 rounded-[14px] bg-rose-50 flex items-center justify-center shrink-0">
          <svg class="w-6 h-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        </div>
        <div>
          <div class="text-[28px] font-bold text-[#1d1d1f] leading-none tracking-tight">{{ summaryStats.failed_simulations }}</div>
          <div class="text-[12px] font-semibold text-[#86868b] tracking-wide uppercase mt-1">Sim. Failures</div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-10">
      <!-- Tabs Sidebar (macOS style segmented logic) -->
      <aside class="flex flex-col gap-1">
        <button 
          v-for="tab in ['drafts', 'in_review', 'approved', 'published', 'archived', 'rollbacks']" 
          :key="tab"
          @click="activeTab = tab"
          class="flex items-center gap-3 px-4 py-3 rounded-[12px] transition-all text-left group"
          :class="activeTab === tab ? 'bg-black/[0.06] text-[#1d1d1f] font-semibold shadow-inner' : 'text-[#86868b] hover:bg-black/[0.03] hover:text-[#1d1d1f] font-medium'"
        >
          <!-- Elegant SVGs for Tabs -->
          <span v-if="tab === 'drafts'" class="w-[18px] h-[18px]" v-html="icons.drafts"></span>
          <span v-else-if="tab === 'in_review'" class="w-[18px] h-[18px]" v-html="icons.in_review"></span>
          <span v-else-if="tab === 'approved'" class="w-[18px] h-[18px]" v-html="icons.approved"></span>
          <span v-else-if="tab === 'published'" class="w-[18px] h-[18px]" v-html="icons.published"></span>
          <span v-else-if="tab === 'archived'" class="w-[18px] h-[18px]" v-html="icons.archived"></span>
          <span v-else class="w-[18px] h-[18px]" v-html="icons.rollbacks"></span>

          <span class="flex-1 text-[13px] leading-none tracking-wide">{{ tab.replace('_', ' ').toUpperCase() }}</span>
          
          <span v-if="versions[tab] && versions[tab].length > 0" class="bg-[#1d1d1f]/10 text-[#1d1d1f] text-[11px] px-2 py-0.5 rounded-full font-bold group-hover:bg-[#1d1d1f]/20 transition-colors">
            {{ versions[tab].length }}
          </span>
        </button>
      </aside>

      <!-- Content Area -->
      <main class="min-h-[500px]">
        <!-- Filters Toolbar -->
        <div class="flex justify-between items-center mb-6 gap-4">
          <div class="relative w-full max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search features..." 
              class="w-full bg-white border border-black/[0.08] rounded-xl py-2 pl-9 pr-4 text-[13px] text-[#1d1d1f] outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-sm transition-all"
            />
          </div>
          <div class="flex items-center gap-3">
            <label class="text-[12px] font-semibold tracking-wide text-[#86868b] uppercase">Sort by</label>
            <select v-model="sortBy" class="bg-white border border-black/[0.08] rounded-xl py-1.5 px-3 pr-8 text-[13px] text-[#1d1d1f] font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 shadow-sm cursor-pointer appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%208l5%205%205-5%22%20fill%3D%22none%22%20stroke%3D%22%2386868b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E')] bg-no-repeat bg-[right_4px_center]">
              <option value="date">Date (Newest)</option>
              <option value="name">Name (A-Z)</option>
            </select>
          </div>
        </div>

        <div v-if="loading" class="flex flex-col items-center justify-center p-24">
            <svg class="animate-spin w-8 h-8 text-black/20 mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            <p class="text-[#86868b] font-medium text-[14px]">Fetching deployment statuses...</p>
        </div>

        <div v-else class="space-y-4">
          <template v-if="activeTab !== 'rollbacks'">
            <div v-if="filteredVersions.length === 0" class="py-24 flex flex-col items-center justify-center border-2 border-dashed border-black/5 rounded-3xl">
              <div class="w-16 h-16 bg-black/[0.02] rounded-2xl flex items-center justify-center mb-4">
                 <svg class="w-8 h-8 text-black/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
              </div>
              <p class="text-[#86868b] font-semibold text-[15px]">{{ searchQuery ? 'No matching versions found.' : 'No items found in this section.' }}</p>
            </div>
            
            <div v-for="v in filteredVersions" :key="v.id" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-black/[0.04] p-5 sm:px-6 sm:py-5 rounded-[20px] shadow-[0_1px_4px_rgba(0,0,0,0.02)] hover:shadow-[0_8px_20px_rgba(0,0,0,0.04)] hover:border-black/[0.08] transition-all group">
              <div>
                <div class="flex items-center gap-3 mb-1.5">
                  <h3 class="text-[17px] font-bold text-[#1d1d1f] tracking-tight">{{ v.feature.name }}</h3>
                  <span class="bg-blue-50 text-blue-600 text-[11px] font-bold px-2 py-0.5 rounded-md tracking-wider">v{{ v.version_no }}</span>
                </div>
                <p class="text-[13px] text-[#86868b] font-medium flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-black/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  Updated {{ formatDate(v.updated_at) }}
                </p>
              </div>
              <div class="flex items-center gap-2">
                <button v-if="activeTab === 'drafts'" @click="continueDraft(v)" class="px-5 py-2 min-w-[140px] bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-bold rounded-xl shadow-sm transition-colors active:scale-95">Continue Design</button>
                <button v-if="activeTab === 'in_review'" @click="viewReview(v.id)" class="px-5 py-2 min-w-[140px] bg-white border border-black/10 hover:border-black/20 hover:bg-gray-50/50 text-[#1d1d1f] text-[13px] font-bold rounded-xl shadow-sm transition-all active:scale-95">Review Impact</button>
                <button v-if="activeTab === 'approved'" @click="viewReview(v.id)" class="px-5 py-2 min-w-[140px] bg-emerald-500 hover:bg-emerald-600 text-white text-[13px] font-bold rounded-xl shadow-sm transition-colors active:scale-95">Publish to Prod</button>
                <button v-if="activeTab === 'published'" @click="viewReview(v.id)" class="px-5 py-2 min-w-[140px] bg-white border border-black/10 hover:border-black/20 hover:bg-gray-50/50 text-[#1d1d1f] text-[13px] font-bold rounded-xl shadow-sm transition-all active:scale-95">Monitor & Rollback</button>
              </div>
            </div>
          </template>

          <template v-else>
            <div v-if="rollbackLogs.length === 0" class="py-24 flex flex-col items-center justify-center border-2 border-dashed border-black/5 rounded-3xl">
              <p class="text-[#86868b] font-semibold text-[15px]">No rollback events to display.</p>
            </div>
            <div v-for="log in rollbackLogs" :key="log.id" class="bg-white border border-rose-100 border-l-4 border-l-rose-500 p-6 rounded-[20px] shadow-sm hover:shadow-md transition-all">
              <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center gap-3">
                    <h3 class="text-[16px] font-bold text-[#1d1d1f] tracking-tight">{{ log.feature_name }}</h3>
                    <span class="bg-rose-50 text-rose-600 text-[11px] font-bold px-2 py-0.5 rounded-md tracking-wider">v{{ log.version_no }}</span>
                  </div>
              </div>
              <p class="text-[14px] text-rose-600/90 italic font-medium leading-relaxed">"{{ log.reason }}"</p>
              <div class="h-px w-full bg-black/5 my-3"></div>
              <p class="text-[12px] text-[#86868b] font-medium flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-black/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                  Rolled back by <b class="text-[#1d1d1f]">{{ log.user_name }}</b> on {{ formatDate(log.rolled_back_at) }}
              </p>
            </div>
          </template>
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
/* Scoped css completely bypassed in favor of unified visual Tailwind config */
.animate-spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
