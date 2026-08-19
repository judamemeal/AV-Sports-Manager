<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-bold text-white mb-6">Calendario</h1>
    <div class="flex flex-wrap gap-3 mb-6">
      <select v-model="champId" @change="fetchCalendar" class="px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
        <option value="">Todos los campeonatos</option>
        <option v-for="c in championships" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
      <select v-model="statusFilter" @change="fetchCalendar" class="px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
        <option value="">Todos</option><option value="scheduled">Programados</option><option value="in_progress">En Juego</option><option value="finished">Finalizados</option>
      </select>
    </div>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <div v-else class="space-y-6">
      <div v-for="(group, date) in groupedByDate" :key="date" class="animate-fade-in">
        <h3 class="text-sm font-semibold text-primary-400 mb-3 uppercase tracking-wider">{{ formatDateHeader(date) }}</h3>
        <div class="space-y-2">
          <router-link v-for="m in group" :key="m.id" :to="`/partidos/${m.id}`" class="card-gradient rounded-xl p-4 hover:border-primary-500/30 transition-all block">
            <div class="flex items-center justify-center gap-2 mb-2">
              <span v-if="!champId" class="text-xs text-primary-400 font-medium">{{ m.championship?.name }}</span>
              <span v-if="!champId && (m.phase?.name || m.round?.name)" class="text-surface-600 text-xs">•</span>
              <span v-if="m.phase?.name || m.round?.name" class="text-[10px] bg-primary-900/30 text-primary-400 px-2 py-0.5 rounded-full uppercase tracking-wider font-bold">
                {{ m.phase?.name }} <span v-if="m.phase?.name && m.round?.name">-</span> {{ m.round?.name }}
              </span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 flex-1"><div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: m.home_team?.color || '#64748b' }">{{ m.home_team?.name?.charAt(0) }}</div><span class="text-sm text-white truncate">{{ m.home_team?.name || 'TBD' }}</span></div>
              <div class="px-4 text-center flex-shrink-0">
                <p v-if="m.status === 'finished' || m.status === 'in_progress'" class="text-lg font-bold text-white">{{ m.home_score }} - {{ m.away_score }}</p>
                <p v-else class="text-sm font-bold text-surface-500">{{ m.match_time || 'VS' }}</p>
              </div>
              <div class="flex items-center gap-2 flex-1 justify-end"><span class="text-sm text-white truncate">{{ m.away_team?.name || 'TBD' }}</span><div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: m.away_team?.color || '#64748b' }">{{ m.away_team?.name?.charAt(0) }}</div></div>
            </div>
          </router-link>
        </div>
      </div>
    </div>
    <p v-if="!loading && !matches.length" class="text-surface-500 text-center py-20">No hay partidos en el calendario.</p>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
const { get, loading } = useApi();
const championships = ref([]);
const matches = ref([]);
const champId = ref('');
const statusFilter = ref('');
const groupedByDate = computed(() => {
  const g = {};
  matches.value.forEach(m => { const d = m.match_date || 'Sin fecha'; if (!g[d]) g[d] = []; g[d].push(m); });
  return g;
});
function formatDateHeader(d) { if (d === 'Sin fecha') return d; return new Date(d).toLocaleDateString('es', { weekday: 'long', day: 'numeric', month: 'long' }); }
async function fetchCalendar() { try { const params = {}; if (champId.value) params.championship_id = champId.value; if (statusFilter.value) params.status = statusFilter.value; const d = await get('/partidos', params); matches.value = d.data || []; } catch {} }
onMounted(async () => { try { const d = await get('/campeonatos'); championships.value = d.data || []; } catch {} await fetchCalendar(); });
</script>
