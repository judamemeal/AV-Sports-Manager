<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-bold text-white mb-6">Cruces y Llaves</h1>
    <select v-if="championships.length" v-model="selectedId" @change="fetchBrackets" class="mb-6 px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
      <option v-for="c in championships" :key="c.id" :value="c.id">{{ c.name }}</option>
    </select>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <template v-else>
      <div v-for="phase in phases" :key="phase.id" class="mb-10 animate-fade-in">
        <h2 class="text-xl font-bold text-white mb-4">{{ phase.name }}</h2>
        <div class="flex flex-wrap gap-6 justify-center">
          <div v-for="round in (phase.rounds || [])" :key="round.id" class="flex-shrink-0">
            <h3 class="text-sm font-semibold text-primary-400 mb-3 text-center">{{ round.name }}</h3>
            <div class="space-y-4">
              <div v-for="m in getMatchesByRound(phase, round.id)" :key="m.id"
                class="card-gradient rounded-xl p-4 w-64 hover:border-primary-500/30 transition-all">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2 flex-1">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: m.home_team?.color || '#475569' }">{{ m.home_team?.name?.charAt(0) || '?' }}</div>
                    <span class="text-sm text-white truncate">{{ m.home_team?.name || 'Por definir' }}</span>
                  </div>
                  <span class="text-sm font-bold" :class="m.status === 'finished' && m.home_score > m.away_score ? 'text-primary-400' : 'text-surface-400'">{{ m.status !== 'scheduled' ? m.home_score : '' }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2 flex-1">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: m.away_team?.color || '#475569' }">{{ m.away_team?.name?.charAt(0) || '?' }}</div>
                    <span class="text-sm text-white truncate">{{ m.away_team?.name || 'Por definir' }}</span>
                  </div>
                  <span class="text-sm font-bold" :class="m.status === 'finished' && m.away_score > m.home_score ? 'text-primary-400' : 'text-surface-400'">{{ m.status !== 'scheduled' ? m.away_score : '' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
    <p v-if="!loading && !phases.length" class="text-surface-500 text-center py-20">No hay cruces generados.</p>
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
const phases = ref([]);
function getMatchesByRound(phase, roundId) { return (phase.matches || []).filter(m => m.round_id === roundId); }
async function fetchBrackets() { if (!selectedId.value) return; try { const d = await get(`/campeonatos/${selectedId.value}/cruces`); phases.value = d.data || []; } catch {} }
onMounted(async () => { try { const d = await get('/campeonatos'); championships.value = d.data || []; if (!selectedId.value && championships.value.length) selectedId.value = championships.value[0].id; await fetchBrackets(); } catch {} });
</script>
