<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <template v-else-if="team">
      <router-link to="/equipos" class="text-sm text-surface-400 hover:text-primary-400 mb-4 inline-block">← Equipos</router-link>
      <div class="card-gradient rounded-xl p-6 mb-6 flex flex-col sm:flex-row items-start gap-6">
        <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-xl" :style="{ backgroundColor: team.color || '#64748b' }">{{ team.name?.charAt(0) }}</div>
        <div class="flex-1">
          <h1 class="text-3xl font-bold text-white">{{ team.name }}</h1>
          <p class="text-surface-400 mt-1">{{ team.course }} {{ team.parallel }} • {{ team.category }}</p>
          <p v-if="team.captain_name" class="text-sm text-surface-500 mt-1">Capitán: {{ team.captain_name }}</p>
        </div>
        <div v-if="teamStats" class="grid grid-cols-4 gap-3 text-center">
          <div><p class="text-xl font-bold text-white">{{ teamStats.played }}</p><p class="text-xs text-surface-500">PJ</p></div>
          <div><p class="text-xl font-bold text-primary-400">{{ teamStats.won }}</p><p class="text-xs text-surface-500">PG</p></div>
          <div><p class="text-xl font-bold text-surface-300">{{ teamStats.drawn }}</p><p class="text-xs text-surface-500">PE</p></div>
          <div><p class="text-xl font-bold text-danger-500">{{ teamStats.lost }}</p><p class="text-xs text-surface-500">PP</p></div>
        </div>
      </div>
      <!-- Players -->
      <h2 class="text-xl font-bold text-white mb-4">Plantilla</h2>
      <div v-if="players.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <router-link v-for="p in players" :key="p.id" :to="`/jugadores/${p.id}`" class="card-gradient rounded-xl p-4 flex items-center gap-4 hover:border-primary-500/30 transition-all">
          <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold" :style="{ backgroundColor: team.color || '#64748b', color: 'white' }">{{ p.jersey_number || '-' }}</div>
          <div class="flex-1 min-w-0"><p class="text-white font-medium truncate">{{ p.full_name }}</p><p class="text-xs text-surface-400">{{ p.position_label }}</p></div>
          <div class="text-right"><p class="text-primary-400 font-bold">{{ p.goals_count || 0 }}</p><p class="text-xs text-surface-500">goles</p></div>
        </router-link>
      </div>
      <p v-else class="text-surface-500 text-center py-8">Sin jugadores registrados.</p>
    </template>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useApi } from '../../composables/useApi.js';
const { get, loading } = useApi();
const route = useRoute();
const team = ref(null);
const players = ref([]);
const teamStats = ref(null);
onMounted(async () => {
  try {
    const id = route.params.id;
    const [t, p, s] = await Promise.all([get(`/equipos/${id}`), get(`/equipos/${id}/jugadores`), get(`/equipos/${id}/estadisticas`).catch(() => ({ data: null }))]);
    team.value = t.data || t;
    players.value = p.data || [];
    teamStats.value = s.data;
  } catch {}
});
</script>
