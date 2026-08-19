<template>
  <div class="animate-fade-in pb-20">
    <!-- Hero / Title -->
    <section class="relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-primary-900/30 via-surface-950 to-accent-900/20" />
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-20 text-center">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight">
          Volta<span class="text-primary-400">Campeonato</span>
        </h1>
        <p class="mt-4 text-lg text-surface-400 max-w-2xl mx-auto">
          Sistema de campeonatos deportivos. Información detallada y en tiempo real.
        </p>
      </div>
    </section>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" />
    </div>

    <!-- Active Championships -->
    <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 space-y-24">
      <div v-for="champ in activeChampionships" :key="champ.id" class="space-y-8 animate-slide-up">
        
        <!-- Championship Header -->
        <div class="card-gradient rounded-2xl p-8 border-t-4 border-primary-500 shadow-2xl relative overflow-hidden">
          <div class="absolute -right-10 -top-10 text-surface-800/20">
            <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
          </div>
          <div class="relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/10 border border-primary-500/20 text-primary-400 text-xs font-medium mb-4">
              <span class="w-2 h-2 rounded-full bg-primary-400 badge-live" />
              Torneo Activo
            </div>
            <h2 class="text-3xl font-extrabold text-white">{{ champ.name }}</h2>
            <div class="flex flex-wrap gap-4 mt-3 text-sm text-surface-400">
              <span v-if="champ.sport" class="bg-surface-800 px-3 py-1 rounded-md border border-surface-700">⚽ {{ champ.sport }}</span>
              <span v-if="champ.category" class="bg-surface-800 px-3 py-1 rounded-md border border-surface-700">👥 {{ champ.category }}</span>
              <span v-if="champ.year" class="bg-surface-800 px-3 py-1 rounded-md border border-surface-700">📅 {{ champ.year }}</span>
            </div>
            <p v-if="champ.description" class="mt-4 text-surface-300 max-w-3xl">{{ champ.description }}</p>
            
            <div v-if="champ.regulations" class="mt-6 p-4 rounded-xl bg-surface-900/50 border border-surface-800">
              <h4 class="text-sm font-bold text-surface-200 mb-2">📜 Reglamento e Información</h4>
              <p class="text-sm text-surface-400 whitespace-pre-line">{{ champ.regulations }}</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          
          <!-- Standings (Col Span 2) -->
          <div class="lg:col-span-2 space-y-6">
            <h3 class="text-2xl font-bold text-white flex items-center gap-3">
              <span class="text-primary-400">📊</span> Tabla de Posiciones
            </h3>
            
            <div v-if="!champ.groupedStandings || !champ.groupedStandings.length" class="card-gradient rounded-xl p-8 text-center border border-surface-800">
              <p class="text-surface-500">No hay datos de posiciones aún para este torneo.</p>
            </div>
            
            <div v-else v-for="group in champ.groupedStandings" :key="group.name" class="card-gradient rounded-xl p-6 border border-surface-800">
              <h4 v-if="group.name" class="text-lg font-bold text-white mb-4">{{ group.name }}</h4>
              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="text-surface-400 text-xs border-b border-surface-700">
                      <th class="text-left py-2 px-2 w-8">POS</th>
                      <th class="text-left py-2 px-2">EQUIPO</th>
                      <th class="text-center py-2 px-1">PJ</th>
                      <th class="text-center py-2 px-1">PG</th>
                      <th class="text-center py-2 px-1">PE</th>
                      <th class="text-center py-2 px-1">PP</th>
                      <th class="text-center py-2 px-1">GF</th>
                      <th class="text-center py-2 px-1">GC</th>
                      <th class="text-center py-2 px-1">DG</th>
                      <th class="text-center py-2 px-1 font-bold text-primary-400">PTS</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(s, i) in group.items" :key="s.id" class="border-b border-surface-800/50 hover:bg-surface-800/30 transition-colors">
                      <td class="py-3 px-2 font-bold" :class="i < 2 ? 'text-primary-400' : 'text-surface-400'">{{ i + 1 }}</td>
                      <td class="py-3 px-2">
                        <div class="flex items-center gap-2">
                          <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: s.team?.color || '#64748b' }">{{ s.team?.name?.charAt(0) }}</div>
                          <router-link :to="`/equipos/${s.team?.id}`" class="text-white font-medium hover:text-primary-400 transition-colors">{{ s.team?.name }}</router-link>
                        </div>
                      </td>
                      <td class="text-center text-surface-300">{{ s.played }}</td>
                      <td class="text-center text-surface-300">{{ s.won }}</td>
                      <td class="text-center text-surface-300">{{ s.drawn }}</td>
                      <td class="text-center text-surface-300">{{ s.lost }}</td>
                      <td class="text-center text-surface-300">{{ s.goals_for }}</td>
                      <td class="text-center text-surface-300">{{ s.goals_against }}</td>
                      <td class="text-center" :class="s.goal_difference > 0 ? 'text-primary-400' : s.goal_difference < 0 ? 'text-danger-500' : 'text-surface-400'">{{ s.goal_difference > 0 ? '+' : '' }}{{ s.goal_difference }}</td>
                      <td class="text-center font-bold text-white text-base">{{ s.points }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Scorers & Matches Sidebar -->
          <div class="space-y-8">
            <!-- Top Scorers -->
            <div>
              <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">🏆 Goleadores</h3>
              <div class="card-gradient rounded-xl p-5 border border-surface-800">
                <div v-if="champ.scorers?.length" class="space-y-3">
                  <div v-for="(scorer, i) in champ.scorers.slice(0, 5)" :key="scorer.id" class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-800/30 transition-colors">
                    <span class="w-6 text-center font-bold" :class="i === 0 ? 'text-warning-500' : i === 1 ? 'text-surface-300' : i === 2 ? 'text-orange-400' : 'text-surface-500'">{{ i + 1 }}</span>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-white truncate">{{ scorer.full_name }}</p>
                      <p class="text-xs text-surface-500 truncate">{{ scorer.team?.name }}</p>
                    </div>
                    <span class="text-lg font-bold text-primary-400">{{ scorer.goals }}</span>
                  </div>
                </div>
                <p v-else class="text-surface-500 text-sm text-center py-4">Sin goles registrados.</p>
              </div>
            </div>

            <!-- Unified Calendar / Matches -->
            <div>
              <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">🗓 Partidos & Calendario</h3>
              <div class="card-gradient rounded-xl p-5 border border-surface-800">
                <div v-if="champ.matches?.length" class="space-y-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                  <router-link v-for="m in champ.matches" :key="m.id" :to="`/partidos/${m.id}`" class="block bg-surface-900/50 rounded-lg p-3 hover:bg-surface-800 transition-colors border border-surface-800 hover:border-primary-500/30">
                    <div class="flex items-center justify-between mb-2">
                      <div class="text-[10px] text-surface-500 uppercase tracking-wider font-semibold">{{ formatDate(m.match_date) }}</div>
                      <div v-if="m.phase?.name || m.round?.name" class="text-[9px] bg-primary-900/30 text-primary-400 px-1.5 py-0.5 rounded-full uppercase font-bold tracking-wider text-right line-clamp-1 max-w-[60%]">
                        {{ m.phase?.name }} <span v-if="m.phase?.name && m.round?.name">-</span> {{ m.round?.name }}
                      </div>
                    </div>
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-2 flex-1 min-w-0">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0" :style="{ backgroundColor: m.home_team?.color || '#64748b' }">{{ m.home_team?.name?.charAt(0) }}</div>
                        <span class="text-xs text-white truncate">{{ m.home_team?.name }}</span>
                      </div>
                      
                      <div class="px-2 text-center flex-shrink-0">
                        <span v-if="m.status === 'finished' || m.status === 'in_progress'" class="text-sm font-bold text-white px-2 py-0.5 rounded bg-surface-800">{{ m.home_score }} - {{ m.away_score }}</span>
                        <span v-else class="text-xs font-bold text-primary-400">{{ m.match_time ? m.match_time.substring(0,5) : 'VS' }}</span>
                      </div>
                      
                      <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
                        <span class="text-xs text-white truncate">{{ m.away_team?.name }}</span>
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0" :style="{ backgroundColor: m.away_team?.color || '#64748b' }">{{ m.away_team?.name?.charAt(0) }}</div>
                      </div>
                    </div>
                  </router-link>
                </div>
                <p v-else class="text-surface-500 text-sm text-center py-4">No hay partidos programados.</p>
              </div>
            </div>
            
          </div>
        </div>

        <div class="h-px bg-gradient-to-r from-transparent via-surface-700 to-transparent my-16"></div>
      </div>
      
      <div v-if="!activeChampionships.length" class="text-center py-32">
        <div class="inline-flex w-16 h-16 rounded-full bg-surface-800 items-center justify-center mb-4">
          <span class="text-3xl">🏆</span>
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">No hay torneos activos</h2>
        <p class="text-surface-400">Actualmente no se está disputando ningún campeonato.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';

const { get, loading } = useApi();
const activeChampionships = ref([]);

function formatDate(d) {
  if (!d) return 'Sin fecha';
  return new Date(d).toLocaleDateString('es', { weekday: 'short', day: 'numeric', month: 'short' });
}

onMounted(async () => {
  try {
    const champData = await get('/campeonatos', { status: 'active' });
    const champs = champData.data || [];
    
    // Fetch details for all active championships in parallel
    const enhancedChamps = await Promise.all(champs.map(async (champ) => {
      const [standingsData, scorersData, matchesData] = await Promise.all([
        get(`/campeonatos/${champ.id}/posiciones`).catch(() => ({ data: [] })),
        get(`/campeonatos/${champ.id}/goleadores`).catch(() => ({ data: [] })),
        get(`/partidos`, { championship_id: champ.id }).catch(() => ({ data: [] }))
      ]);
      
      const allStandings = standingsData.data || [];
      const groups = {};
      allStandings.forEach(s => { 
        const gn = s.group?.name || 'General'; 
        if (!groups[gn]) groups[gn] = { name: gn === 'General' ? '' : gn, items: [] }; 
        groups[gn].items.push(s); 
      });

      return {
        ...champ,
        groupedStandings: Object.values(groups),
        scorers: scorersData.data || [],
        matches: matchesData.data || []
      };
    }));
    
    activeChampionships.value = enhancedChamps;
  } catch (err) {
    console.error('Error loading home:', err);
  }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #334155;
  border-radius: 20px;
}
</style>
