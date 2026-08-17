<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <template v-else-if="match">
      <router-link to="/partidos" class="text-sm text-surface-400 hover:text-primary-400 mb-4 inline-block">← Partidos</router-link>
      <!-- Scoreboard -->
      <div :class="['card-gradient rounded-2xl p-8 mb-6 text-center', match.status === 'in_progress' ? 'glow-live' : '']">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs text-surface-500">{{ match.phase?.name }} {{ match.round?.name ? '• ' + match.round.name : '' }}</span>
          <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', match.status === 'in_progress' ? 'bg-green-500/10 text-green-400 badge-live' : match.status === 'finished' ? 'bg-surface-600/30 text-surface-400' : 'bg-blue-500/10 text-blue-400']">{{ match.status_label }}</span>
        </div>
        <div class="flex items-center justify-center gap-8 mt-6">
          <div class="text-center">
            <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center text-white text-2xl font-bold" :style="{ backgroundColor: match.home_team?.color || '#64748b' }">{{ match.home_team?.name?.charAt(0) }}</div>
            <p class="text-white font-bold mt-3">{{ match.home_team?.name || 'Por definir' }}</p>
          </div>
          <div class="text-center">
            <p class="text-5xl font-extrabold text-white">{{ match.home_score }} <span class="text-surface-600">—</span> {{ match.away_score }}</p>
            <p class="text-xs text-surface-500 mt-2">{{ formatDate(match.match_date) }} • {{ match.match_time || '--:--' }}</p>
            <p v-if="match.venue" class="text-xs text-surface-500">📍 {{ match.venue }}</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center text-white text-2xl font-bold" :style="{ backgroundColor: match.away_team?.color || '#64748b' }">{{ match.away_team?.name?.charAt(0) }}</div>
            <p class="text-white font-bold mt-3">{{ match.away_team?.name || 'Por definir' }}</p>
          </div>
        </div>
      </div>
      <!-- Events Timeline -->
      <div v-if="match.events?.length" class="card-gradient rounded-xl p-6">
        <h2 class="text-lg font-bold text-white mb-4">Cronología</h2>
        <div class="space-y-3">
          <div v-for="e in match.events" :key="e.id" class="flex items-center gap-4 p-3 rounded-lg bg-surface-800/30">
            <span class="text-2xl">{{ e.type_icon }}</span>
            <div class="flex-1">
              <p class="text-sm text-white font-medium">{{ e.player?.full_name || 'Jugador' }}</p>
              <p class="text-xs text-surface-500">{{ e.team?.name }} {{ e.description ? '• ' + e.description : '' }}</p>
            </div>
            <span class="text-sm font-bold text-primary-400">{{ e.minute }}'</span>
          </div>
        </div>
      </div>
      <p v-else-if="match.status === 'finished'" class="card-gradient rounded-xl p-6 text-surface-500 text-center">Sin eventos registrados.</p>
    </template>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useApi } from '../../composables/useApi.js';
const { get, loading } = useApi();
const route = useRoute();
const match = ref(null);
function formatDate(d) { if (!d) return ''; return new Date(d).toLocaleDateString('es', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }
onMounted(async () => { try { const d = await get(`/partidos/${route.params.id}`); match.value = d.data || d; } catch {} });
</script>
