<template>
  <div class="animate-fade-in">
    <!-- KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
      <div v-for="kpi in kpis" :key="kpi.label" class="card-gradient rounded-xl p-4">
        <p class="text-2xl font-bold" :class="kpi.colorClass">{{ kpi.value }}</p>
        <p class="text-xs text-surface-400 mt-1">{{ kpi.label }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: Live + Upcoming -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Live Matches -->
        <div v-if="data?.live_matches?.length" class="card-gradient rounded-xl p-6">
          <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 badge-live" /> Partidos en Vivo
          </h3>
          <div class="space-y-3">
            <router-link v-for="m in data.live_matches" :key="m.id" :to="`/admin/partidos/${m.id}/jugar`"
              class="flex items-center justify-between p-4 rounded-lg bg-surface-800/50 glow-live hover:bg-surface-800 transition-all">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: m.home_team?.color }">{{ m.home_team?.name?.charAt(0) }}</div>
                <span class="text-sm text-white">{{ m.home_team?.name }}</span>
              </div>
              <span class="text-xl font-bold text-white">{{ m.home_score }} - {{ m.away_score }}</span>
              <div class="flex items-center gap-2">
                <span class="text-sm text-white">{{ m.away_team?.name }}</span>
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: m.away_team?.color }">{{ m.away_team?.name?.charAt(0) }}</div>
              </div>
            </router-link>
          </div>
        </div>

        <!-- Upcoming Matches -->
        <div class="card-gradient rounded-xl p-6">
          <h3 class="text-lg font-bold text-white mb-4">Próximos Partidos</h3>
          <div v-if="data?.upcoming_matches?.length" class="space-y-2">
            <div v-for="m in data.upcoming_matches" :key="m.id"
              class="flex items-center justify-between p-3 rounded-lg bg-surface-800/30 hover:bg-surface-800/50 transition-colors">
              <div class="flex items-center gap-2 flex-1">
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: m.home_team?.color }">{{ m.home_team?.name?.charAt(0) }}</div>
                <span class="text-sm text-white">{{ m.home_team?.name }}</span>
              </div>
              <div class="text-center px-3">
                <p class="text-xs text-surface-500">{{ m.match_date }} • {{ m.match_time || '--:--' }}</p>
                <p class="text-sm font-bold text-surface-400">VS</p>
              </div>
              <div class="flex items-center gap-2 flex-1 justify-end">
                <span class="text-sm text-white">{{ m.away_team?.name }}</span>
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: m.away_team?.color }">{{ m.away_team?.name?.charAt(0) }}</div>
              </div>
            </div>
          </div>
          <p v-else class="text-surface-500 text-sm text-center py-4">No hay partidos próximos.</p>
        </div>
      </div>

      <!-- Right: Quick info -->
      <div class="space-y-6">
        <!-- Top Team -->
        <div v-if="data?.top_team" class="card-gradient rounded-xl p-6">
          <h3 class="text-lg font-bold text-white mb-3">🏆 Equipo Líder</h3>
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold" :style="{ backgroundColor: data.top_team.color }">
              {{ data.top_team.name?.charAt(0) }}
            </div>
            <div>
              <p class="text-white font-semibold">{{ data.top_team.name }}</p>
              <p class="text-primary-400 text-sm font-bold">{{ data.top_team.points }} pts</p>
            </div>
          </div>
        </div>

        <!-- Top Scorer -->
        <div v-if="data?.top_scorer" class="card-gradient rounded-xl p-6">
          <h3 class="text-lg font-bold text-white mb-3">⚽ Máximo Goleador</h3>
          <div>
            <p class="text-white font-semibold">{{ data.top_scorer.name }}</p>
            <p class="text-surface-400 text-sm">{{ data.top_scorer.team }}</p>
            <p class="text-2xl font-bold text-primary-400 mt-1">{{ data.top_scorer.goals }} goles</p>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card-gradient rounded-xl p-6">
          <h3 class="text-lg font-bold text-white mb-4">Acciones Rápidas</h3>
          <div class="space-y-2">
            <router-link to="/admin/campeonatos/crear" class="block w-full py-2.5 px-4 rounded-lg bg-primary-600 text-white text-sm font-medium text-center hover:bg-primary-500 transition-colors">
              + Nuevo Campeonato
            </router-link>
            <router-link to="/admin/equipos/crear" class="block w-full py-2.5 px-4 rounded-lg bg-surface-700 text-white text-sm font-medium text-center hover:bg-surface-600 transition-colors">
              + Nuevo Equipo
            </router-link>
            <router-link to="/admin/partidos/crear" class="block w-full py-2.5 px-4 rounded-lg bg-surface-700 text-white text-sm font-medium text-center hover:bg-surface-600 transition-colors">
              + Nuevo Partido
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-10">
      <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';

const { get, loading } = useApi();
const data = ref(null);

const kpis = computed(() => {
  if (!data.value) return [];
  return [
    { label: 'Campeonatos Activos', value: data.value.active_championships || 0, colorClass: 'text-primary-400' },
    { label: 'Equipos', value: data.value.total_teams || 0, colorClass: 'text-accent-400' },
    { label: 'Jugadores', value: data.value.total_players || 0, colorClass: 'text-blue-400' },
    { label: 'Partidos Jugados', value: data.value.matches_played || 0, colorClass: 'text-green-400' },
    { label: 'Pendientes', value: data.value.matches_pending || 0, colorClass: 'text-warning-500' },
    { label: 'Goles', value: data.value.total_goals || 0, colorClass: 'text-orange-400' },
  ];
});

onMounted(async () => {
  try {
    const res = await get('/admin/dashboard');
    data.value = res.data;
  } catch {}
});
</script>
