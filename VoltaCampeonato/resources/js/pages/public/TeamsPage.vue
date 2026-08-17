<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-bold text-white mb-6">Equipos</h1>
    <div class="mb-6"><input v-model="search" @input="fetchTeams" placeholder="Buscar equipo..." class="w-full max-w-md px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white placeholder-surface-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50 transition-all" /></div>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <router-link v-for="t in teams" :key="t.id" :to="`/equipos/${t.id}`" class="card-gradient rounded-xl p-5 hover:border-primary-500/30 transition-all group animate-fade-in">
        <div class="flex items-center gap-4"><div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl font-bold shadow-lg" :style="{ backgroundColor: t.color || '#64748b' }">{{ t.name?.charAt(0) }}</div>
          <div><p class="text-white font-bold group-hover:text-primary-400 transition-colors">{{ t.name }}</p><p class="text-xs text-surface-400">{{ t.course }} {{ t.parallel }}</p><p class="text-xs text-surface-500">{{ t.players_count || 0 }} jugadores</p></div></div>
      </router-link>
    </div>
    <p v-if="!loading && !teams.length" class="text-surface-500 text-center py-20">No se encontraron equipos.</p>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
const { get, loading } = useApi();
const teams = ref([]);
const search = ref('');
let timeout;
async function fetchTeams() { clearTimeout(timeout); timeout = setTimeout(async () => { try { const d = await get('/equipos', { search: search.value }); teams.value = d.data || []; } catch {} }, 300); }
onMounted(fetchTeams);
</script>
