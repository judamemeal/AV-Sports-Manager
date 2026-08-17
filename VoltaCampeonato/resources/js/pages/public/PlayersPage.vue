<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-bold text-white mb-6">Jugadores</h1>
    <div class="flex flex-wrap gap-3 mb-6">
      <input v-model="search" @input="fetchPlayers" placeholder="Buscar jugador..." class="flex-1 min-w-[200px] px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white placeholder-surface-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50 transition-all" />
      <select v-model="position" @change="fetchPlayers" class="px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
        <option value="">Todas las posiciones</option>
        <option value="goalkeeper">Portero</option><option value="defender">Defensa</option><option value="midfielder">Mediocampista</option><option value="forward">Delantero</option>
      </select>
    </div>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <router-link v-for="p in players" :key="p.id" :to="`/jugadores/${p.id}`" class="card-gradient rounded-xl p-4 flex items-center gap-4 hover:border-primary-500/30 transition-all group animate-fade-in">
        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold" :style="{ backgroundColor: p.team?.color || '#64748b' }">{{ p.jersey_number || '?' }}</div>
        <div class="flex-1 min-w-0"><p class="text-white font-semibold truncate group-hover:text-primary-400 transition-colors">{{ p.full_name }}</p><p class="text-xs text-surface-400">{{ p.position_label }} • {{ p.team?.name }}</p></div>
        <div class="text-right"><p class="text-lg font-bold text-primary-400">{{ p.goals_count || 0 }}</p><p class="text-xs text-surface-500">goles</p></div>
      </router-link>
    </div>
    <p v-if="!loading && !players.length" class="text-surface-500 text-center py-20">No se encontraron jugadores.</p>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
const { get, loading } = useApi();
const players = ref([]);
const search = ref('');
const position = ref('');
let timeout;
async function fetchPlayers() { clearTimeout(timeout); timeout = setTimeout(async () => { try { const d = await get('/jugadores', { search: search.value, position: position.value }); players.value = d.data || []; } catch {} }, 300); }
onMounted(fetchPlayers);
</script>
