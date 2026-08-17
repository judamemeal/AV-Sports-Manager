<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <template v-else-if="player">
      <router-link to="/jugadores" class="text-sm text-surface-400 hover:text-primary-400 mb-4 inline-block">← Jugadores</router-link>
      <div class="card-gradient rounded-xl p-6 mb-6">
        <div class="flex items-start gap-6">
          <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-3xl font-bold" :style="{ backgroundColor: player.team?.color || '#64748b' }">{{ player.jersey_number || '?' }}</div>
          <div class="flex-1">
            <h1 class="text-3xl font-bold text-white">{{ player.full_name }}</h1>
            <p class="text-surface-400 mt-1">{{ player.position_label }}</p>
            <router-link v-if="player.team" :to="`/equipos/${player.team.id}`" class="text-sm text-primary-400 hover:text-primary-300 mt-1 inline-block">{{ player.team.name }} →</router-link>
            <p v-if="player.course" class="text-xs text-surface-500 mt-2">{{ player.course }} {{ player.parallel }}</p>
          </div>
        </div>
      </div>
      <!-- Stats -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="card-gradient rounded-xl p-4 text-center"><p class="text-2xl font-bold text-white">{{ stats?.matches_played || 0 }}</p><p class="text-xs text-surface-400 mt-1">Partidos</p></div>
        <div class="card-gradient rounded-xl p-4 text-center"><p class="text-2xl font-bold text-primary-400">{{ stats?.goals || 0 }}</p><p class="text-xs text-surface-400 mt-1">Goles</p></div>
        <div class="card-gradient rounded-xl p-4 text-center"><p class="text-2xl font-bold text-warning-500">{{ stats?.yellow_cards || 0 }}</p><p class="text-xs text-surface-400 mt-1">🟨 Amarillas</p></div>
        <div class="card-gradient rounded-xl p-4 text-center"><p class="text-2xl font-bold text-danger-500">{{ stats?.red_cards || 0 }}</p><p class="text-xs text-surface-400 mt-1">🟥 Rojas</p></div>
      </div>
      <!-- Recent events -->
      <div v-if="stats?.recent_events?.length" class="card-gradient rounded-xl p-6">
        <h2 class="text-lg font-bold text-white mb-4">Últimos Eventos</h2>
        <div class="space-y-3">
          <div v-for="(e, i) in stats.recent_events" :key="i" class="flex items-center gap-3 p-2 rounded-lg bg-surface-800/30">
            <span class="text-xl">{{ e.type_icon }}</span>
            <div class="flex-1"><p class="text-sm text-white">{{ e.match?.home_team }} vs {{ e.match?.away_team }}</p><p class="text-xs text-surface-500">{{ e.match?.date }} • Min {{ e.minute }}'</p></div>
          </div>
        </div>
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
const player = ref(null);
const stats = ref(null);
onMounted(async () => {
  try {
    const id = route.params.id;
    const [p, s] = await Promise.all([get(`/jugadores/${id}`), get(`/jugadores/${id}/estadisticas`).catch(() => ({ data: null }))]);
    player.value = p.data || p;
    stats.value = s.data;
  } catch {}
});
</script>
