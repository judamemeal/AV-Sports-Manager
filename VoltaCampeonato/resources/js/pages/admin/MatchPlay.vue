<template>
  <div class="animate-fade-in">
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <template v-else-if="match">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <router-link to="/admin/partidos" class="text-sm text-surface-400 hover:text-primary-400">← Partidos</router-link>
        <span v-if="match.status === 'in_progress'" class="flex items-center gap-2 px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-xs font-medium"><span class="w-2 h-2 rounded-full bg-green-400 badge-live" /> EN VIVO</span>
        <span v-else-if="match.status === 'finished'" class="px-3 py-1 rounded-full bg-surface-600/30 text-surface-400 text-xs font-medium">FINALIZADO</span>
      </div>

      <!-- Scoreboard -->
      <div :class="['card-gradient rounded-2xl p-8 mb-6 text-center', match.status === 'in_progress' ? 'glow-live' : '']">
        <!-- Timer -->
        <div v-if="match.status === 'in_progress'" class="mb-4">
          <p class="text-4xl font-mono font-bold text-primary-400">{{ timerDisplay }}</p>
          <div class="flex justify-center gap-2 mt-2">
            <button @click="toggleTimer" class="px-3 py-1 rounded-lg text-xs font-medium" :class="timerRunning ? 'bg-warning-500/20 text-warning-500' : 'bg-primary-500/20 text-primary-400'">
              {{ timerRunning ? '⏸ Pausar' : '▶ Reanudar' }}
            </button>
            <button @click="resetTimer(0)" class="px-3 py-1 rounded-lg bg-surface-700 text-surface-300 text-xs">1T (00:00)</button>
            <button @click="resetTimer(45)" class="px-3 py-1 rounded-lg bg-surface-700 text-surface-300 text-xs">2T (45:00)</button>
          </div>
        </div>

        <div class="flex items-center justify-center gap-8">
          <div class="text-center flex-1">
            <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center text-white text-3xl font-bold shadow-xl" :style="{ backgroundColor: match.home_team?.color || '#64748b' }">{{ match.home_team?.name?.charAt(0) }}</div>
            <p class="text-white font-bold text-lg mt-3">{{ match.home_team?.name }}</p>
          </div>
          <div class="text-center">
            <p class="text-6xl font-extrabold text-white tabular-nums">{{ match.home_score }} <span class="text-surface-600">—</span> {{ match.away_score }}</p>
          </div>
          <div class="text-center flex-1">
            <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center text-white text-3xl font-bold shadow-xl" :style="{ backgroundColor: match.away_team?.color || '#64748b' }">{{ match.away_team?.name?.charAt(0) }}</div>
            <p class="text-white font-bold text-lg mt-3">{{ match.away_team?.name }}</p>
          </div>
        </div>

        <!-- Start / Finish buttons -->
        <div class="mt-6">
          <button v-if="match.status === 'scheduled'" @click="startMatch" :disabled="actionLoading" class="px-8 py-3 rounded-xl bg-green-600 text-white font-bold text-lg hover:bg-green-500 transition-all shadow-lg hover:shadow-green-500/30 disabled:opacity-50">
            ▶ INICIAR PARTIDO
          </button>
          <button v-if="match.status === 'in_progress'" @click="showFinishModal = true" class="px-8 py-3 rounded-xl bg-danger-600 text-white font-bold hover:bg-danger-500 transition-all shadow-lg">
            🏁 FINALIZAR PARTIDO
          </button>
        </div>
      </div>

      <!-- Event Buttons (only during match) -->
      <div v-if="match.status === 'in_progress'" class="grid grid-cols-3 gap-4 mb-6">
        <button @click="openEventModal('goal')" class="card-gradient rounded-xl p-4 text-center hover:border-primary-500/40 transition-all group">
          <span class="text-3xl">⚽</span>
          <p class="text-white font-semibold mt-2 group-hover:text-primary-400 transition-colors">Gol</p>
        </button>
        <button @click="openEventModal('yellow_card')" class="card-gradient rounded-xl p-4 text-center hover:border-warning-500/40 transition-all group">
          <span class="text-3xl">🟨</span>
          <p class="text-white font-semibold mt-2 group-hover:text-warning-500 transition-colors">Tarjeta Amarilla</p>
        </button>
        <button @click="openEventModal('red_card')" class="card-gradient rounded-xl p-4 text-center hover:border-danger-500/40 transition-all group">
          <span class="text-3xl">🟥</span>
          <p class="text-white font-semibold mt-2 group-hover:text-danger-500 transition-colors">Tarjeta Roja</p>
        </button>
      </div>

      <!-- Events Timeline -->
      <div class="card-gradient rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Cronología del Partido</h3>
        <div v-if="events.length" class="space-y-3">
          <div v-for="e in events" :key="e.id" class="flex items-center gap-4 p-3 rounded-lg" :class="e.team_id === match.home_team_id ? 'bg-surface-800/30' : 'bg-surface-800/50'">
            <span class="text-2xl">{{ e.type_icon }}</span>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-white font-medium">{{ e.player?.full_name || 'Jugador' }}</p>
              <p class="text-xs text-surface-500">{{ e.team?.name }} {{ e.description ? '• ' + e.description : '' }}</p>
            </div>
            <span class="text-lg font-bold text-primary-400 tabular-nums">{{ e.minute }}'</span>
          </div>
        </div>
        <p v-else class="text-surface-500 text-sm text-center py-8">No hay eventos registrados.</p>
      </div>
    </template>

    <!-- Event Modal -->
    <Teleport to="body">
      <div v-if="showEventModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="showEventModal = false" />
        <div class="relative card-gradient rounded-xl p-6 max-w-md w-full animate-fade-in">
          <h3 class="text-lg font-bold text-white mb-4">{{ eventTypeLabels[eventForm.type] }}</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-surface-300 mb-1.5">Equipo *</label>
              <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="eventForm.team_id = match.home_team_id; loadEventPlayers()" :class="['p-3 rounded-lg border text-sm font-medium transition-all flex items-center gap-2', eventForm.team_id === match.home_team_id ? 'border-primary-500 bg-primary-500/10 text-primary-400' : 'border-surface-700 text-surface-300 hover:border-surface-500']">
                  <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: match.home_team?.color }">{{ match.home_team?.name?.charAt(0) }}</div>
                  {{ match.home_team?.name }}
                </button>
                <button type="button" @click="eventForm.team_id = match.away_team_id; loadEventPlayers()" :class="['p-3 rounded-lg border text-sm font-medium transition-all flex items-center gap-2', eventForm.team_id === match.away_team_id ? 'border-primary-500 bg-primary-500/10 text-primary-400' : 'border-surface-700 text-surface-300 hover:border-surface-500']">
                  <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: match.away_team?.color }">{{ match.away_team?.name?.charAt(0) }}</div>
                  {{ match.away_team?.name }}
                </button>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-surface-300 mb-1.5">Jugador</label>
              <select v-model="eventForm.player_id" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
                <option value="">Seleccionar jugador...</option>
                <option v-for="p in eventPlayers" :key="p.id" :value="p.id">#{{ p.jersey_number }} {{ p.full_name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-surface-300 mb-1.5">Minuto *</label>
              <input v-model.number="eventForm.minute" type="number" min="0" max="200" required class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" />
            </div>
            <div>
              <label class="block text-sm font-medium text-surface-300 mb-1.5">Descripción</label>
              <input v-model="eventForm.description" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" placeholder="Tiro libre, penal, etc." />
            </div>
          </div>
          <div class="flex gap-3 justify-end mt-6">
            <button @click="showEventModal = false" class="px-4 py-2 rounded-lg text-sm text-surface-300 hover:bg-surface-800">Cancelar</button>
            <button @click="recordEvent" :disabled="actionLoading || !eventForm.team_id" class="px-6 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-500 disabled:opacity-50">
              {{ actionLoading ? 'Registrando...' : 'Registrar' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Finish Modal -->
    <Teleport to="body">
      <div v-if="showFinishModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="showFinishModal = false" />
        <div class="relative card-gradient rounded-xl p-6 max-w-sm w-full animate-fade-in">
          <h3 class="text-lg font-bold text-white mb-2">🏁 ¿Finalizar partido?</h3>
          <p class="text-sm text-surface-400 mb-2">Resultado actual:</p>
          <p class="text-2xl font-bold text-white text-center mb-4">{{ match.home_team?.name }} {{ match.home_score }} — {{ match.away_score }} {{ match.away_team?.name }}</p>
          <p class="text-xs text-surface-500 mb-6">Se actualizarán las posiciones y clasificaciones automáticamente.</p>
          <div class="flex gap-3 justify-end">
            <button @click="showFinishModal = false" class="px-4 py-2 rounded-lg text-sm text-surface-300 hover:bg-surface-800">Cancelar</button>
            <button @click="finishMatch" :disabled="actionLoading" class="px-6 py-2 rounded-lg bg-danger-600 text-white text-sm font-medium hover:bg-danger-500 disabled:opacity-50">
              {{ actionLoading ? 'Finalizando...' : 'Finalizar' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi.js';
import { useNotificationStore } from '../../stores/notification.js';

const { get, post, loading } = useApi();
const notify = useNotificationStore();
const route = useRoute();
const router = useRouter();

const match = ref(null);
const events = ref([]);
const actionLoading = ref(false);

// Timer
const timerSeconds = ref(0);
const timerRunning = ref(false);
let timerInterval = null;
const timerDisplay = ref('00:00');

function updateTimerDisplay() {
  const mins = Math.floor(timerSeconds.value / 60);
  const secs = timerSeconds.value % 60;
  timerDisplay.value = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

function toggleTimer() {
  if (timerRunning.value) {
    clearInterval(timerInterval);
    timerRunning.value = false;
  } else {
    timerInterval = setInterval(() => {
      timerSeconds.value++;
      updateTimerDisplay();
    }, 1000);
    timerRunning.value = true;
  }
}

function resetTimer(startMinute) {
  clearInterval(timerInterval);
  timerSeconds.value = startMinute * 60;
  timerRunning.value = false;
  updateTimerDisplay();
}

// Event Modal
const showEventModal = ref(false);
const showFinishModal = ref(false);
const eventPlayers = ref([]);
const eventForm = reactive({ type: 'goal', team_id: null, player_id: '', minute: 0, description: '' });

const eventTypeLabels = {
  goal: '⚽ Registrar Gol',
  yellow_card: '🟨 Tarjeta Amarilla',
  red_card: '🟥 Tarjeta Roja',
  substitution: '🔄 Sustitución',
};

function openEventModal(type) {
  eventForm.type = type;
  eventForm.team_id = null;
  eventForm.player_id = '';
  eventForm.minute = Math.floor(timerSeconds.value / 60);
  eventForm.description = '';
  eventPlayers.value = [];
  showEventModal.value = true;
}

function loadEventPlayers() {
  if (!eventForm.team_id) return;
  const team = eventForm.team_id === match.value.home_team_id ? match.value.home_team : match.value.away_team;
  eventPlayers.value = team?.players || [];
}

async function startMatch() {
  actionLoading.value = true;
  try {
    const d = await post(`/partidos/${match.value.id}/iniciar`);
    match.value = d.data?.data || d.data;
    if (match.value.events) events.value = match.value.events;
    resetTimer(0);
    toggleTimer();
    notify.success('¡Partido iniciado!');
  } catch {}
  actionLoading.value = false;
}

async function recordEvent() {
  if (!eventForm.team_id) return;
  actionLoading.value = true;
  try {
    const d = await post(`/partidos/${match.value.id}/eventos`, {
      type: eventForm.type,
      team_id: eventForm.team_id,
      player_id: eventForm.player_id || null,
      minute: eventForm.minute,
      description: eventForm.description || null,
    });
    // Update score
    if (d.score) {
      match.value.home_score = d.score.home;
      match.value.away_score = d.score.away;
    }
    // Add event to timeline
    if (d.event) {
      events.value.push(d.event);
      events.value.sort((a, b) => a.minute - b.minute);
    }
    showEventModal.value = false;
    notify.success('Evento registrado.');
  } catch {}
  actionLoading.value = false;
}

async function finishMatch() {
  actionLoading.value = true;
  try {
    const d = await post(`/partidos/${match.value.id}/finalizar`);
    match.value = d.data?.data || d.data;
    clearInterval(timerInterval);
    timerRunning.value = false;
    showFinishModal.value = false;
    notify.success('Partido finalizado. Posiciones actualizadas.');
  } catch {}
  actionLoading.value = false;
}

onMounted(async () => {
  try {
    const d = await get(`/partidos/${route.params.id}`);
    match.value = d.data || d;
    events.value = match.value.events || [];
    if (match.value.status === 'in_progress') {
      resetTimer(0);
    }
  } catch {
    router.push('/admin/partidos');
  }
});

onUnmounted(() => { clearInterval(timerInterval); });
</script>
