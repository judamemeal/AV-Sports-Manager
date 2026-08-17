<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-bold text-white mb-8">Campeonatos</h1>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <router-link v-for="c in championships" :key="c.id" :to="`/campeonatos/${c.id}`"
        class="card-gradient rounded-xl p-6 hover:border-primary-500/30 transition-all group animate-fade-in">
        <div class="flex items-center justify-between mb-4">
          <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', c.status === 'active' ? 'bg-green-500/10 text-green-400' : c.status === 'upcoming' ? 'bg-blue-500/10 text-blue-400' : 'bg-surface-600/30 text-surface-400']">
            {{ c.status_label }}
          </span>
          <span class="text-surface-500 text-sm">{{ c.year }}</span>
        </div>
        <h3 class="text-lg font-bold text-white group-hover:text-primary-400 transition-colors">{{ c.name }}</h3>
        <p class="text-sm text-surface-400 mt-2">{{ c.sport }} • {{ c.category }}</p>
        <div class="flex gap-4 mt-4 text-xs text-surface-500">
          <span>{{ c.teams_count || 0 }} equipos</span>
          <span>{{ c.matches_count || 0 }} partidos</span>
        </div>
      </router-link>
    </div>
    <p v-if="!loading && !championships.length" class="text-surface-500 text-center py-20">No hay campeonatos registrados.</p>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
const { get, loading } = useApi();
const championships = ref([]);
onMounted(async () => { try { const d = await get('/campeonatos'); championships.value = d.data || []; } catch {} });
</script>
