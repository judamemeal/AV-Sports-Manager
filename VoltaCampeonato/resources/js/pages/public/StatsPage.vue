<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-bold text-white mb-6">Estadísticas</h1>
    <select v-if="championships.length" v-model="selectedId" @change="fetchAll" class="mb-6 px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
      <option v-for="c in championships" :key="c.id" :value="c.id">{{ c.name }}</option>
    </select>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <template v-else>
      <!-- KPIs -->
      <div v-if="stats" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
        <div class="card-gradient rounded-xl p-4 text-center"><p class="text-2xl font-bold text-white">{{ stats.total_teams }}</p><p class="text-xs text-surface-400 mt-1">Equipos</p></div>
        <div class="card-gradient rounded-xl p-4 text-center"><p class="text-2xl font-bold text-accent-400">{{ stats.total_players }}</p><p class="text-xs text-surface-400 mt-1">Jugadores</p></div>
        <div class="card-gradient rounded-xl p-4 text-center"><p class="text-2xl font-bold text-primary-400">{{ stats.total_matches }}</p><p class="text-xs text-surface-400 mt-1">Partidos</p></div>
        <div class="card-gradient rounded-xl p-4 text-center"><p class="text-2xl font-bold text-warning-500">{{ stats.total_goals }}</p><p class="text-xs text-surface-400 mt-1">Goles</p></div>
        <div class="card-gradient rounded-xl p-4 text-center"><p class="text-2xl font-bold text-yellow-400">{{ stats.total_yellow_cards }}</p><p class="text-xs text-surface-400 mt-1">🟨 Amarillas</p></div>
        <div class="card-gradient rounded-xl p-4 text-center"><p class="text-2xl font-bold text-danger-500">{{ stats.total_red_cards }}</p><p class="text-xs text-surface-400 mt-1">🟥 Rojas</p></div>
      </div>
      <!-- Top Scorers -->
      <div class="card-gradient rounded-xl p-6 animate-fade-in">
        <h2 class="text-xl font-bold text-white mb-4">🏆 Top Goleadores</h2>
        <div v-if="scorers.length" class="space-y-3">
          <div v-for="(s, i) in scorers" :key="s.id" class="flex items-center gap-4 p-3 rounded-lg hover:bg-surface-800/30 transition-colors">
            <span class="w-10 text-center text-xl font-bold" :class="i === 0 ? 'text-warning-500' : i === 1 ? 'text-surface-300' : i === 2 ? 'text-orange-400' : 'text-surface-500'">{{ i + 1 }}</span>
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold" :style="{ backgroundColor: s.team?.color || '#64748b' }">{{ s.full_name?.charAt(0) }}</div>
            <div class="flex-1 min-w-0"><p class="text-white font-semibold truncate">{{ s.full_name }}</p><p class="text-xs text-surface-500">{{ s.team?.name }}</p></div>
            <div class="text-right"><p class="text-2xl font-bold text-primary-400">{{ s.goals }}</p><p class="text-xs text-surface-500">goles</p></div>
          </div>
        </div>
        <p v-else class="text-surface-500 text-center py-8">Sin goles registrados.</p>
      </div>
    </template>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useApi } from '../../composables/useApi.js';
const { get, loading } = useApi();
const route = useRoute();
const championships = ref([]);
const selectedId = ref(route.params.id || null);
const stats = ref(null);
const scorers = ref([]);
async function fetchAll() {
  if (!selectedId.value) return;
  try {
    const [st, sc] = await Promise.all([get(`/campeonatos/${selectedId.value}/estadisticas`), get(`/campeonatos/${selectedId.value}/goleadores`)]);
    stats.value = st.data;
    scorers.value = sc.data || [];
  } catch {}
}
onMounted(async () => {
  try { const d = await get('/campeonatos'); championships.value = d.data || []; if (!selectedId.value && championships.value.length) selectedId.value = championships.value[0].id; await fetchAll(); } catch {}
});
</script>
