<template>
  <div>
    <!-- Hero -->
    <section class="relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-primary-900/30 via-surface-950 to-accent-900/20" />
      <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 40px 40px;" />

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-16 sm:py-24">
        <div class="text-center animate-fade-in">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/10 border border-primary-500/20 text-primary-400 text-xs font-medium mb-6">
            <span class="w-2 h-2 rounded-full bg-primary-400 badge-live" />
            Campeonato Activo
          </div>
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
            {{ championship?.name || 'VoltaCampeonato' }}
          </h1>
          <p class="mt-4 text-lg text-surface-400 max-w-2xl mx-auto">
            {{ championship?.description || 'Sistema de campeonatos deportivos de la Unidad Educativa' }}
          </p>

          <div v-if="championship" class="mt-8 flex flex-wrap justify-center gap-4">
            <div class="glass rounded-xl px-5 py-3 text-center">
              <p class="text-2xl font-bold text-primary-400">{{ stats?.total_teams || 0 }}</p>
              <p class="text-xs text-surface-400 mt-1">Equipos</p>
            </div>
            <div class="glass rounded-xl px-5 py-3 text-center">
              <p class="text-2xl font-bold text-accent-400">{{ stats?.total_matches || 0 }}</p>
              <p class="text-xs text-surface-400 mt-1">Partidos</p>
            </div>
            <div class="glass rounded-xl px-5 py-3 text-center">
              <p class="text-2xl font-bold text-warning-500">{{ stats?.total_goals || 0 }}</p>
              <p class="text-xs text-surface-400 mt-1">Goles</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6">
      <!-- Live Matches -->
      <section v-if="liveMatches.length" class="mt-10 animate-slide-up">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-green-500 badge-live" /> En Vivo
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <router-link v-for="m in liveMatches" :key="m.id" :to="`/partidos/${m.id}`"
            class="card-gradient rounded-xl p-5 glow-live hover:scale-[1.02] transition-transform cursor-pointer">
            <div class="flex items-center justify-between">
              <div class="text-center flex-1">
                <div class="w-10 h-10 rounded-full mx-auto flex items-center justify-center text-white font-bold text-sm" :style="{ backgroundColor: m.home_team?.color || '#64748b' }">
                  {{ m.home_team?.name?.charAt(0) }}
                </div>
                <p class="text-sm font-medium text-white mt-2 truncate">{{ m.home_team?.name }}</p>
              </div>
              <div class="px-4 text-center">
                <p class="text-3xl font-extrabold text-white">{{ m.home_score }} <span class="text-surface-500">-</span> {{ m.away_score }}</p>
                <span class="inline-flex items-center gap-1 text-xs text-green-400 font-medium mt-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-green-400 badge-live" /> EN VIVO
                </span>
              </div>
              <div class="text-center flex-1">
                <div class="w-10 h-10 rounded-full mx-auto flex items-center justify-center text-white font-bold text-sm" :style="{ backgroundColor: m.away_team?.color || '#64748b' }">
                  {{ m.away_team?.name?.charAt(0) }}
                </div>
                <p class="text-sm font-medium text-white mt-2 truncate">{{ m.away_team?.name }}</p>
              </div>
            </div>
          </router-link>
        </div>
      </section>

      <!-- Upcoming Matches -->
      <section v-if="upcomingMatches.length" class="mt-12 animate-slide-up">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold text-white">Próximos Partidos</h2>
          <router-link to="/partidos" class="text-sm text-primary-400 hover:text-primary-300">Ver todos →</router-link>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <router-link v-for="m in upcomingMatches" :key="m.id" :to="`/partidos/${m.id}`"
            class="card-gradient rounded-xl p-5 hover:border-primary-500/30 transition-all cursor-pointer group">
            <div class="text-xs text-surface-500 mb-3">
              {{ formatDate(m.match_date) }} • {{ m.match_time || '--:--' }} • {{ m.venue || 'Por definir' }}
            </div>
            <div class="flex items-center justify-between">
              <div class="text-center flex-1">
                <div class="w-10 h-10 rounded-full mx-auto flex items-center justify-center text-white font-bold text-sm" :style="{ backgroundColor: m.home_team?.color || '#64748b' }">
                  {{ m.home_team?.name?.charAt(0) }}
                </div>
                <p class="text-sm font-medium text-white mt-2 truncate">{{ m.home_team?.name }}</p>
              </div>
              <div class="px-4 text-center">
                <span class="text-lg font-bold text-surface-500 group-hover:text-primary-400 transition-colors">VS</span>
              </div>
              <div class="text-center flex-1">
                <div class="w-10 h-10 rounded-full mx-auto flex items-center justify-center text-white font-bold text-sm" :style="{ backgroundColor: m.away_team?.color || '#64748b' }">
                  {{ m.away_team?.name?.charAt(0) }}
                </div>
                <p class="text-sm font-medium text-white mt-2 truncate">{{ m.away_team?.name }}</p>
              </div>
            </div>
          </router-link>
        </div>
      </section>

      <!-- Standings + Top Scorers -->
      <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Standings -->
        <div class="lg:col-span-2 card-gradient rounded-xl p-6 animate-slide-up">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-white">Tabla de Posiciones</h2>
            <router-link v-if="championship" :to="`/posiciones/${championship.id}`" class="text-sm text-primary-400 hover:text-primary-300">Completa →</router-link>
          </div>
          <div v-if="standings.length" class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="text-surface-400 text-xs border-b border-surface-700">
                  <th class="text-left py-2 px-2">#</th>
                  <th class="text-left py-2 px-2">Equipo</th>
                  <th class="text-center py-2 px-1">PJ</th>
                  <th class="text-center py-2 px-1">PG</th>
                  <th class="text-center py-2 px-1">PE</th>
                  <th class="text-center py-2 px-1">PP</th>
                  <th class="text-center py-2 px-1">DG</th>
                  <th class="text-center py-2 px-1 font-bold text-primary-400">PTS</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(s, i) in standings.slice(0, 8)" :key="s.id" class="border-b border-surface-800/50 hover:bg-surface-800/30 transition-colors">
                  <td class="py-2.5 px-2 font-bold" :class="i < 2 ? 'text-primary-400' : 'text-surface-400'">{{ i + 1 }}</td>
                  <td class="py-2.5 px-2">
                    <div class="flex items-center gap-2">
                      <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: s.team?.color || '#64748b' }">
                        {{ s.team?.name?.charAt(0) }}
                      </div>
                      <span class="text-white font-medium">{{ s.team?.name }}</span>
                    </div>
                  </td>
                  <td class="text-center text-surface-300 py-2.5 px-1">{{ s.played }}</td>
                  <td class="text-center text-surface-300 py-2.5 px-1">{{ s.won }}</td>
                  <td class="text-center text-surface-300 py-2.5 px-1">{{ s.drawn }}</td>
                  <td class="text-center text-surface-300 py-2.5 px-1">{{ s.lost }}</td>
                  <td class="text-center py-2.5 px-1" :class="s.goal_difference > 0 ? 'text-primary-400' : s.goal_difference < 0 ? 'text-danger-500' : 'text-surface-400'">
                    {{ s.goal_difference > 0 ? '+' : '' }}{{ s.goal_difference }}
                  </td>
                  <td class="text-center font-bold text-white py-2.5 px-1">{{ s.points }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-else class="text-surface-500 text-sm py-8 text-center">No hay datos de posiciones aún.</p>
        </div>

        <!-- Top Scorers -->
        <div class="card-gradient rounded-xl p-6 animate-slide-up">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-white">🏆 Goleadores</h2>
            <router-link v-if="championship" :to="`/estadisticas/${championship.id}`" class="text-sm text-primary-400 hover:text-primary-300">Ver más →</router-link>
          </div>
          <div v-if="scorers.length" class="space-y-3">
            <div v-for="(scorer, i) in scorers.slice(0, 8)" :key="scorer.id"
              class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-800/30 transition-colors">
              <span class="w-6 text-center font-bold" :class="i === 0 ? 'text-warning-500' : i === 1 ? 'text-surface-300' : i === 2 ? 'text-orange-400' : 'text-surface-500'">
                {{ i + 1 }}
              </span>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ scorer.full_name }}</p>
                <p class="text-xs text-surface-500 truncate">{{ scorer.team?.name }}</p>
              </div>
              <span class="text-lg font-bold text-primary-400">{{ scorer.goals }}</span>
            </div>
          </div>
          <p v-else class="text-surface-500 text-sm py-8 text-center">Sin goles registrados.</p>
        </div>
      </div>

      <!-- Recent Results -->
      <section v-if="recentResults.length" class="mt-12 mb-12 animate-slide-up">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold text-white">Últimos Resultados</h2>
          <router-link to="/partidos" class="text-sm text-primary-400 hover:text-primary-300">Ver todos →</router-link>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <router-link v-for="m in recentResults" :key="m.id" :to="`/partidos/${m.id}`"
            class="card-gradient rounded-xl p-4 hover:border-surface-600 transition-all cursor-pointer">
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-2 flex-1 min-w-0">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" :style="{ backgroundColor: m.home_team?.color || '#64748b' }">
                  {{ m.home_team?.name?.charAt(0) }}
                </div>
                <span class="text-sm text-white truncate">{{ m.home_team?.name }}</span>
              </div>
              <div class="text-center px-2 flex-shrink-0">
                <span class="text-base font-bold text-white">{{ m.home_score }} - {{ m.away_score }}</span>
              </div>
              <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
                <span class="text-sm text-white truncate">{{ m.away_team?.name }}</span>
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" :style="{ backgroundColor: m.away_team?.color || '#64748b' }">
                  {{ m.away_team?.name?.charAt(0) }}
                </div>
              </div>
            </div>
          </router-link>
        </div>
      </section>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';

const { get, loading } = useApi();

const championship = ref(null);
const stats = ref(null);
const standings = ref([]);
const scorers = ref([]);
const upcomingMatches = ref([]);
const liveMatches = ref([]);
const recentResults = ref([]);

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('es', { day: 'numeric', month: 'short' });
}

onMounted(async () => {
  try {
    // Get active championship
    const champData = await get('/campeonatos', { status: 'active' });
    const champs = champData.data || [];
    if (champs.length) {
      championship.value = champs[0];
      const id = championship.value.id;

      // Load data in parallel
      const [standingsData, scorersData, statsData, matchesData, liveData] = await Promise.all([
        get(`/campeonatos/${id}/posiciones`).catch(() => ({ data: [] })),
        get(`/campeonatos/${id}/goleadores`).catch(() => ({ data: [] })),
        get(`/campeonatos/${id}/estadisticas`).catch(() => ({ data: {} })),
        get(`/partidos`, { championship_id: id }).catch(() => ({ data: [] })),
        get(`/partidos/en-vivo`).catch(() => ({ data: [] })),
      ]);

      standings.value = standingsData.data || [];
      scorers.value = scorersData.data || [];
      stats.value = statsData.data || {};
      liveMatches.value = liveData.data || [];

      const allMatches = matchesData.data || [];
      upcomingMatches.value = allMatches.filter(m => m.status === 'scheduled').slice(0, 6);
      recentResults.value = allMatches.filter(m => m.status === 'finished').reverse().slice(0, 4);
    }
  } catch (err) {
    console.error('Error loading home:', err);
  }
});
</script>
