<template>
  <div class="animate-fade-in">
    <h2 class="text-2xl font-bold text-white mb-6">Tabla de Posiciones</h2>
    <div class="flex gap-3 mb-6">
      <select v-model="champId" @change="fetchStandings" class="px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
        <option value="">Seleccionar campeonato...</option>
        <option v-for="c in championships" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <div v-else-if="groupedStandings.length" class="space-y-8">
      <div v-for="group in groupedStandings" :key="group.name" class="card-gradient rounded-xl p-6">
        <h3 v-if="group.name" class="text-lg font-bold text-white mb-4">{{ group.name }}</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm"><thead><tr class="text-surface-400 text-xs border-b border-surface-700">
            <th class="text-left py-2 px-2 w-8">POS</th><th class="text-left py-2 px-2">EQUIPO</th>
            <th class="text-center py-2 px-1">PJ</th><th class="text-center py-2 px-1">PG</th><th class="text-center py-2 px-1">PE</th><th class="text-center py-2 px-1">PP</th>
            <th class="text-center py-2 px-1">GF</th><th class="text-center py-2 px-1">GC</th><th class="text-center py-2 px-1">DG</th><th class="text-center py-2 px-1 font-bold text-primary-400">PTS</th>
          </tr></thead><tbody>
            <tr v-for="(s, i) in group.items" :key="s.id" class="border-b border-surface-800/50 hover:bg-surface-800/30">
              <td class="py-2.5 px-2 font-bold" :class="i < 2 ? 'text-primary-400' : 'text-surface-400'">{{ i + 1 }}</td>
              <td class="py-2.5 px-2"><div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: s.team?.color || '#64748b' }">{{ s.team?.name?.charAt(0) }}</div><span class="text-white font-medium">{{ s.team?.name }}</span></div></td>
              <td class="text-center text-surface-300">{{ s.played }}</td><td class="text-center text-surface-300">{{ s.won }}</td><td class="text-center text-surface-300">{{ s.drawn }}</td><td class="text-center text-surface-300">{{ s.lost }}</td>
              <td class="text-center text-surface-300">{{ s.goals_for }}</td><td class="text-center text-surface-300">{{ s.goals_against }}</td>
              <td class="text-center" :class="s.goal_difference > 0 ? 'text-primary-400' : s.goal_difference < 0 ? 'text-danger-500' : 'text-surface-400'">{{ s.goal_difference > 0 ? '+' : '' }}{{ s.goal_difference }}</td>
              <td class="text-center font-bold text-white">{{ s.points }}</td>
            </tr>
          </tbody></table>
        </div>
      </div>
    </div>
    <p v-else class="text-surface-500 text-center py-20 card-gradient rounded-xl">Selecciona un campeonato para ver las posiciones.</p>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
const { get, loading } = useApi();
const championships = ref([]); const champId = ref(''); const groupedStandings = ref([]);
async function fetchStandings() {
  if (!champId.value) { groupedStandings.value = []; return; }
  try {
    const d = await get(`/campeonatos/${champId.value}/posiciones`);
    const all = d.data || [];
    const groups = {};
    all.forEach(s => { const gn = s.group?.name || 'General'; if (!groups[gn]) groups[gn] = { name: gn === 'General' ? '' : gn, items: [] }; groups[gn].items.push(s); });
    groupedStandings.value = Object.values(groups);
  } catch {}
}
onMounted(async () => { try { const d = await get('/campeonatos'); championships.value = d.data || []; } catch {} });
</script>
