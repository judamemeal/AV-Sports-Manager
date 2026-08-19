<template>
  <div class="animate-fade-in max-w-6xl mx-auto pb-20">
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <template v-else-if="match">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <router-link to="/admin/partidos" class="text-sm text-surface-400 hover:text-primary-400">← Partidos</router-link>
        <span v-if="match.status === 'in_progress'" class="flex items-center gap-2 px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-xs font-medium"><span class="w-2 h-2 rounded-full bg-green-400 badge-live" /> EN VIVO</span>
        <span v-else-if="match.status === 'finished'" class="px-3 py-1 rounded-full bg-surface-600/30 text-surface-400 text-xs font-medium">FINALIZADO</span>
      </div>

      <!-- Main Layout: Scoreboard & Players -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Match Control & Players -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Scoreboard -->
          <div :class="['card-gradient rounded-2xl p-8 text-center shadow-xl border border-surface-800 relative', match.status === 'in_progress' ? 'glow-live' : '']">
            <div class="flex flex-col md:flex-row items-center justify-center gap-8 md:gap-16">
              <!-- Home Team -->
              <div class="text-center flex-1">
                <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center text-white text-3xl font-bold shadow-lg mb-3" :style="{ backgroundColor: match.home_team?.color || '#64748b' }">{{ match.home_team?.name?.charAt(0) }}</div>
                <p class="text-white font-bold text-lg">{{ match.home_team?.name }}</p>
              </div>
              
              <!-- Score Display -->
              <div class="flex items-center gap-6">
                <div class="w-20 h-24 bg-surface-900 border-b-4 border-primary-500 flex items-center justify-center rounded-xl shadow-inner">
                  <span class="text-5xl font-extrabold text-white">{{ form.home_score }}</span>
                </div>
                <span class="text-3xl text-surface-600 font-bold">—</span>
                <div class="w-20 h-24 bg-surface-900 border-b-4 border-primary-500 flex items-center justify-center rounded-xl shadow-inner">
                  <span class="text-5xl font-extrabold text-white">{{ form.away_score }}</span>
                </div>
              </div>

              <!-- Away Team -->
              <div class="text-center flex-1">
                <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center text-white text-3xl font-bold shadow-lg mb-3" :style="{ backgroundColor: match.away_team?.color || '#64748b' }">{{ match.away_team?.name?.charAt(0) }}</div>
                <p class="text-white font-bold text-lg">{{ match.away_team?.name }}</p>
              </div>
            </div>

            <!-- Match Actions -->
            <div class="mt-10 border-t border-surface-800 pt-6">
              <button v-if="match.status === 'scheduled'" @click="startMatch" :disabled="actionLoading" class="px-8 py-3 rounded-xl bg-green-600 text-white font-bold text-sm hover:bg-green-500 transition-all shadow-lg hover:shadow-green-500/30 disabled:opacity-50">
                ▶ INICIAR PARTIDO
              </button>
              <div v-else-if="match.status === 'in_progress'" class="flex gap-4 justify-center">
                <button @click="showFinishModal = true" class="px-8 py-3 rounded-xl bg-danger-600 text-white font-bold hover:bg-danger-500 transition-all shadow-lg disabled:opacity-50">
                  🏁 FINALIZAR PARTIDO
                </button>
              </div>
              <div v-else-if="match.status === 'finished'">
                <p class="text-surface-400 font-medium text-sm">Este partido ya ha sido finalizado.</p>
              </div>
            </div>
          </div>

          <!-- Players Lists for Events (Only visible when in progress) -->
          <div v-if="match.status === 'in_progress'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Home Players -->
            <div class="card-gradient rounded-xl border border-surface-800 p-4">
              <h3 class="text-sm font-bold text-white mb-4 text-center border-b border-surface-700 pb-2" :style="{ color: match.home_team?.color || '#fff' }">
                {{ match.home_team?.name }}
              </h3>
              <div class="space-y-2 max-h-96 overflow-y-auto custom-scrollbar pr-2">
                <div v-for="player in match.home_team?.players" :key="player.id" class="flex flex-col gap-2 p-2 rounded-lg bg-surface-900/50 hover:bg-surface-800 transition-colors">
                  <span class="text-sm font-medium text-white truncate">{{ player.name }} {{ player.last_name }} <span class="text-xs text-surface-500">#{{ player.shirt_number || '-' }}</span></span>
                  <div class="flex items-center gap-1">
                    <button @click="addEvent(player.id, match.home_team_id, 'goal')" :disabled="actionLoading" class="flex-1 py-1 rounded bg-surface-700 hover:bg-primary-600 text-sm transition-colors" title="Anotar Gol">⚽</button>
                    <button @click="addEvent(player.id, match.home_team_id, 'yellow_card')" :disabled="actionLoading" class="flex-1 py-1 rounded bg-surface-700 hover:bg-warning-600 text-sm transition-colors" title="Amarilla">🟨</button>
                    <button @click="addEvent(player.id, match.home_team_id, 'red_card')" :disabled="actionLoading" class="flex-1 py-1 rounded bg-surface-700 hover:bg-danger-600 text-sm transition-colors" title="Roja">🟥</button>
                  </div>
                </div>
                <p v-if="!match.home_team?.players?.length" class="text-xs text-surface-500 text-center py-4">No hay jugadores registrados en este equipo.</p>
              </div>
            </div>

            <!-- Away Players -->
            <div class="card-gradient rounded-xl border border-surface-800 p-4">
              <h3 class="text-sm font-bold text-white mb-4 text-center border-b border-surface-700 pb-2" :style="{ color: match.away_team?.color || '#fff' }">
                {{ match.away_team?.name }}
              </h3>
              <div class="space-y-2 max-h-96 overflow-y-auto custom-scrollbar pr-2">
                <div v-for="player in match.away_team?.players" :key="player.id" class="flex flex-col gap-2 p-2 rounded-lg bg-surface-900/50 hover:bg-surface-800 transition-colors">
                  <span class="text-sm font-medium text-white truncate">{{ player.name }} {{ player.last_name }} <span class="text-xs text-surface-500">#{{ player.shirt_number || '-' }}</span></span>
                  <div class="flex items-center gap-1">
                    <button @click="addEvent(player.id, match.away_team_id, 'goal')" :disabled="actionLoading" class="flex-1 py-1 rounded bg-surface-700 hover:bg-primary-600 text-sm transition-colors" title="Anotar Gol">⚽</button>
                    <button @click="addEvent(player.id, match.away_team_id, 'yellow_card')" :disabled="actionLoading" class="flex-1 py-1 rounded bg-surface-700 hover:bg-warning-600 text-sm transition-colors" title="Amarilla">🟨</button>
                    <button @click="addEvent(player.id, match.away_team_id, 'red_card')" :disabled="actionLoading" class="flex-1 py-1 rounded bg-surface-700 hover:bg-danger-600 text-sm transition-colors" title="Roja">🟥</button>
                  </div>
                </div>
                <p v-if="!match.away_team?.players?.length" class="text-xs text-surface-500 text-center py-4">No hay jugadores registrados en este equipo.</p>
              </div>
            </div>

          </div>
        </div>

        <!-- Right: Events History -->
        <div class="lg:col-span-1">
          <div class="card-gradient rounded-xl p-5 border border-surface-800 h-full max-h-[800px] flex flex-col">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">📝 Historial de Eventos</h3>
            <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-3">
              
              <div v-if="eventsList.length" v-for="event in reversedEvents" :key="event.id" class="flex items-start gap-3 p-3 rounded-lg bg-surface-900/50 border border-surface-800 hover:border-surface-600 transition-colors group">
                <div class="text-xl pt-0.5">
                  <span v-if="event.type === 'goal'">⚽</span>
                  <span v-else-if="event.type === 'yellow_card'">🟨</span>
                  <span v-else-if="event.type === 'red_card'">🟥</span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-white truncate">{{ event.player?.name }} {{ event.player?.last_name }}</p>
                  <p class="text-xs text-surface-400 truncate">{{ event.team?.name }}</p>
                </div>
                <button v-if="match.status === 'in_progress'" @click="removeEvent(event.id)" :disabled="actionLoading" class="text-surface-500 hover:text-danger-500 p-1 rounded-md opacity-0 group-hover:opacity-100 transition-all">
                  ❌
                </button>
              </div>

              <p v-else class="text-surface-500 text-sm text-center py-10">No hay eventos registrados aún.</p>
            </div>
          </div>
        </div>
        
      </div>
    </template>

    <!-- Finish Modal -->
    <Teleport to="body">
      <div v-if="showFinishModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="showFinishModal = false" />
        <div class="relative card-gradient rounded-xl p-6 max-w-sm w-full animate-fade-in">
          <h3 class="text-lg font-bold text-white mb-2">🏁 ¿Finalizar partido?</h3>
          <p class="text-sm text-surface-400 mb-2">Resultado final:</p>
          <p class="text-2xl font-bold text-white text-center mb-4">{{ match.home_team?.name }} {{ form.home_score }} — {{ form.away_score }} {{ match.away_team?.name }}</p>
          <p class="text-xs text-surface-500 mb-6">Se actualizarán las posiciones y estadísticas de los jugadores automáticamente.</p>
          <div class="flex gap-3 justify-end">
            <button @click="showFinishModal = false" class="px-4 py-2 rounded-lg text-sm text-surface-300 hover:bg-surface-800">Cancelar</button>
            <button @click="finishMatch" :disabled="actionLoading" class="px-6 py-2 rounded-lg bg-danger-600 text-white text-sm font-medium hover:bg-danger-500 disabled:opacity-50">
              {{ actionLoading ? 'Finalizando...' : 'Confirmar' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi.js';
import { useNotificationStore } from '../../stores/notification.js';

const { get, post, del, loading } = useApi();
const notify = useNotificationStore();
const route = useRoute();
const router = useRouter();

const match = ref(null);
const eventsList = ref([]);
const actionLoading = ref(false);
const showFinishModal = ref(false);

const form = reactive({
  home_score: 0,
  away_score: 0
});

const reversedEvents = computed(() => {
  return [...eventsList.value].reverse();
});

async function fetchMatch() {
  try {
    const d = await get(`/partidos/${route.params.id}`);
    match.value = d.data || d;
    form.home_score = match.value.home_score || 0;
    form.away_score = match.value.away_score || 0;
    eventsList.value = match.value.events || [];
  } catch {
    router.push('/admin/partidos');
  }
}

async function startMatch() {
  actionLoading.value = true;
  try {
    const d = await post(`/partidos/${match.value.id}/iniciar`);
    match.value = d.data?.data || d.data;
    form.home_score = match.value.home_score || 0;
    form.away_score = match.value.away_score || 0;
    eventsList.value = match.value.events || [];
    notify.success('¡Partido iniciado!');
  } catch {}
  actionLoading.value = false;
}

async function addEvent(playerId, teamId, type) {
  actionLoading.value = true;
  try {
    const d = await post(`/partidos/${match.value.id}/eventos`, {
      player_id: playerId,
      team_id: teamId,
      type: type,
      minute: 1 // Default minute since it doesn't matter for this workflow
    });
    
    // Add event to local list
    if (d.event) {
      eventsList.value.push(d.event);
    }
    
    // Update score
    if (d.score) {
      form.home_score = d.score.home;
      form.away_score = d.score.away;
    }
    
    notify.success(type === 'goal' ? '¡Gol registrado!' : 'Tarjeta registrada.');
  } catch {}
  actionLoading.value = false;
}

async function removeEvent(eventId) {
  actionLoading.value = true;
  try {
    const d = await del(`/partidos/${match.value.id}/eventos/${eventId}`);
    
    // Remove from local list
    eventsList.value = eventsList.value.filter(e => e.id !== eventId);
    
    // Update score
    if (d.score) {
      form.home_score = d.score.home;
      form.away_score = d.score.away;
    }
    
    notify.success('Evento eliminado.');
  } catch {}
  actionLoading.value = false;
}

async function finishMatch() {
  actionLoading.value = true;
  try {
    const d = await post(`/partidos/${match.value.id}/finalizar`, {
      home_score: form.home_score,
      away_score: form.away_score
    });
    match.value = d.data?.data || d.data;
    showFinishModal.value = false;
    notify.success('Partido finalizado. Posiciones actualizadas.');
  } catch {}
  actionLoading.value = false;
}

onMounted(async () => {
  await fetchMatch();
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
