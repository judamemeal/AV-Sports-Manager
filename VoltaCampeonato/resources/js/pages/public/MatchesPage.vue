<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-bold text-white mb-6">Partidos</h1>
    <div class="flex flex-wrap gap-3 mb-6">
      <select v-model="statusFilter" @change="fetchMatches" class="px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
        <option value="">Todos los estados</option>
        <option value="scheduled">Programados</option><option value="in_progress">En Juego</option><option value="finished">Finalizados</option><option value="suspended">Suspendidos</option>
      </select>
      <input v-model="dateFilter" @change="fetchMatches" type="date" class="px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" />
    </div>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <div v-else class="space-y-4">
      <router-link v-for="m in matches" :key="m.id" :to="`/partidos/${m.id}`"
        :class="['card-gradient rounded-xl p-5 block hover:border-primary-500/30 transition-all animate-fade-in', m.status === 'in_progress' ? 'glow-live' : '']">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs text-surface-500">{{ m.phase?.name || '' }} {{ m.round?.name ? '• ' + m.round.name : '' }}</span>
          <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusClass(m.status)]">{{ m.status_label }}</span>
        </div>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3 flex-1">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm" :style="{ backgroundColor: m.home_team?.color || '#64748b' }">{{ m.home_team?.name?.charAt(0) || '?' }}</div>
            <span class="text-white font-semibold">{{ m.home_team?.name || 'Por definir' }}</span>
          </div>
          <div class="px-6 text-center">
            <p v-if="m.status === 'finished' || m.status === 'in_progress'" class="text-2xl font-bold text-white">{{ m.home_score }} — {{ m.away_score }}</p>
            <p v-else class="text-lg font-bold text-surface-500">VS</p>
            <p class="text-xs text-surface-500 mt-1">{{ formatDate(m.match_date) }} • {{ m.match_time || '--:--' }}</p>
          </div>
          <div class="flex items-center gap-3 flex-1 justify-end">
            <span class="text-white font-semibold">{{ m.away_team?.name || 'Por definir' }}</span>
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm" :style="{ backgroundColor: m.away_team?.color || '#64748b' }">{{ m.away_team?.name?.charAt(0) || '?' }}</div>
          </div>
        </div>
      </router-link>
    </div>
    <p v-if="!loading && !matches.length" class="text-surface-500 text-center py-20">No se encontraron partidos.</p>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
const { get, loading } = useApi();
const matches = ref([]);
const statusFilter = ref('');
const dateFilter = ref('');
function formatDate(d) { if (!d) return ''; return new Date(d).toLocaleDateString('es', { day: 'numeric', month: 'short', year: 'numeric' }); }
function statusClass(s) { return { scheduled: 'bg-blue-500/10 text-blue-400', in_progress: 'bg-green-500/10 text-green-400', finished: 'bg-surface-600/30 text-surface-400', suspended: 'bg-warning-500/10 text-warning-500', cancelled: 'bg-danger-500/10 text-danger-500' }[s] || ''; }
async function fetchMatches() { try { const d = await get('/partidos', { status: statusFilter.value, date: dateFilter.value }); matches.value = d.data || []; } catch {} }
onMounted(fetchMatches);
</script>
