<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <template v-else-if="championship">
      <div class="mb-8">
        <router-link to="/campeonatos" class="text-sm text-surface-400 hover:text-primary-400 mb-2 inline-block">← Campeonatos</router-link>
        <h1 class="text-3xl font-bold text-white">{{ championship.name }}</h1>
        <p class="text-surface-400 mt-1">{{ championship.sport }} • {{ championship.category }} • {{ championship.year }}</p>
      </div>
      <!-- Tabs -->
      <div class="flex gap-1 mb-6 overflow-x-auto pb-2">
        <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
          :class="['px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all', activeTab === tab.key ? 'bg-primary-600 text-white' : 'text-surface-400 hover:text-white hover:bg-surface-800']">
          {{ tab.label }}
        </button>
      </div>
      <!-- Info -->
      <div v-if="activeTab === 'info'" class="card-gradient rounded-xl p-6 animate-fade-in">
        <p class="text-surface-300" v-if="championship.description">{{ championship.description }}</p>
        <div v-if="championship.regulations" class="mt-4 p-4 rounded-lg bg-surface-800/50">
          <h3 class="text-sm font-semibold text-white mb-2">Reglamento</h3>
          <p class="text-surface-400 text-sm whitespace-pre-wrap">{{ championship.regulations }}</p>
        </div>
      </div>
      <!-- Teams -->
      <div v-if="activeTab === 'teams'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 animate-fade-in">
        <router-link v-for="t in championship.teams" :key="t.id" :to="`/equipos/${t.id}`"
          class="card-gradient rounded-xl p-4 flex items-center gap-4 hover:border-primary-500/30 transition-all">
          <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold" :style="{ backgroundColor: t.color || '#64748b' }">{{ t.name?.charAt(0) }}</div>
          <div><p class="text-white font-semibold">{{ t.name }}</p><p class="text-xs text-surface-400">{{ t.course }} {{ t.parallel }}</p></div>
        </router-link>
      </div>
      <!-- Standings -->
      <div v-if="activeTab === 'standings'" class="card-gradient rounded-xl p-6 animate-fade-in">
        <div v-if="standings.length" class="overflow-x-auto">
          <table class="w-full text-sm"><thead><tr class="text-surface-400 text-xs border-b border-surface-700">
            <th class="text-left py-2 px-2">#</th><th class="text-left py-2 px-2">Equipo</th>
            <th class="text-center py-2">PJ</th><th class="text-center py-2">PG</th><th class="text-center py-2">PE</th><th class="text-center py-2">PP</th>
            <th class="text-center py-2">GF</th><th class="text-center py-2">GC</th><th class="text-center py-2">DG</th><th class="text-center py-2 text-primary-400 font-bold">PTS</th>
          </tr></thead><tbody>
            <tr v-for="(s, i) in standings" :key="s.id" class="border-b border-surface-800/50 hover:bg-surface-800/30">
              <td class="py-2.5 px-2 font-bold" :class="i < 2 ? 'text-primary-400' : 'text-surface-400'">{{ i + 1 }}</td>
              <td class="py-2.5 px-2"><div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: s.team?.color || '#64748b' }">{{ s.team?.name?.charAt(0) }}</div><span class="text-white font-medium">{{ s.team?.name }}</span></div></td>
              <td class="text-center text-surface-300">{{ s.played }}</td><td class="text-center text-surface-300">{{ s.won }}</td>
              <td class="text-center text-surface-300">{{ s.drawn }}</td><td class="text-center text-surface-300">{{ s.lost }}</td>
              <td class="text-center text-surface-300">{{ s.goals_for }}</td><td class="text-center text-surface-300">{{ s.goals_against }}</td>
              <td class="text-center" :class="s.goal_difference > 0 ? 'text-primary-400' : s.goal_difference < 0 ? 'text-danger-500' : 'text-surface-400'">{{ s.goal_difference > 0 ? '+' : '' }}{{ s.goal_difference }}</td>
              <td class="text-center font-bold text-white">{{ s.points }}</td>
            </tr>
          </tbody></table>
        </div>
        <p v-else class="text-surface-500 text-center py-8">Sin datos de posiciones.</p>
      </div>
      <!-- Scorers -->
      <div v-if="activeTab === 'scorers'" class="card-gradient rounded-xl p-6 animate-fade-in">
        <div v-if="scorers.length" class="space-y-3">
          <div v-for="(s, i) in scorers" :key="s.id" class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-800/30">
            <span class="w-8 text-center font-bold" :class="i === 0 ? 'text-warning-500 text-xl' : i < 3 ? 'text-surface-300' : 'text-surface-500'">{{ i + 1 }}</span>
            <div class="flex-1"><p class="text-sm font-medium text-white">{{ s.full_name }}</p><p class="text-xs text-surface-500">{{ s.team?.name }}</p></div>
            <span class="text-lg font-bold text-primary-400">{{ s.goals }}</span>
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
const championship = ref(null);
const standings = ref([]);
const scorers = ref([]);
const activeTab = ref('info');
const tabs = [{ key: 'info', label: 'Información' },{ key: 'teams', label: 'Equipos' },{ key: 'standings', label: 'Posiciones' },{ key: 'scorers', label: 'Goleadores' }];
onMounted(async () => {
  try {
    const id = route.params.id;
    const [c, st, sc] = await Promise.all([get(`/campeonatos/${id}`), get(`/campeonatos/${id}/posiciones`).catch(() => ({data:[]})), get(`/campeonatos/${id}/goleadores`).catch(() => ({data:[]}))]);
    championship.value = c.data || c;
    standings.value = st.data || [];
    scorers.value = sc.data || [];
  } catch {}
});
</script>
