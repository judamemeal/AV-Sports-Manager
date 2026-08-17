<template>
  <div class="animate-fade-in">
    <router-link to="/admin/campeonatos" class="text-sm text-surface-400 hover:text-primary-400 mb-4 inline-block">← Campeonatos</router-link>
    <h2 class="text-2xl font-bold text-white mb-6">Constructor de Formato</h2>

    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <template v-else>
      <!-- Championship info -->
      <div v-if="championship" class="card-gradient rounded-xl p-4 mb-6 flex items-center justify-between">
        <div><p class="text-white font-bold">{{ championship.name }}</p><p class="text-sm text-surface-400">{{ championship.sport }} • {{ championship.teams_count || 0 }} equipos</p></div>
        <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', championship.status === 'active' ? 'bg-green-500/10 text-green-400' : 'bg-blue-500/10 text-blue-400']">{{ championship.status_label }}</span>
      </div>

      <!-- Existing format -->
      <div v-if="existingFormat" class="card-gradient rounded-xl p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-white">Formato Actual</h3>
          <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', existingFormat.status === 'generated' ? 'bg-green-500/10 text-green-400' : 'bg-blue-500/10 text-blue-400']">{{ existingFormat.status_label }}</span>
        </div>
        <p class="text-surface-300">Tipo: <span class="text-white font-medium">{{ existingFormat.type_label }}</span></p>
        <div v-if="existingFormat.phases?.length" class="mt-4 space-y-2">
          <div v-for="phase in existingFormat.phases" :key="phase.id" class="p-3 rounded-lg bg-surface-800/30 flex items-center justify-between">
            <div><p class="text-white text-sm font-medium">{{ phase.name }}</p><p class="text-xs text-surface-500">{{ phase.type_label }} • {{ phase.team_count }} equipos</p></div>
            <span v-if="phase.is_completed" class="text-xs text-green-400">✓ Completada</span>
          </div>
        </div>
      </div>

      <!-- Format Builder -->
      <div v-if="!existingFormat" class="space-y-6">
        <!-- Step 1: Format Type -->
        <div class="card-gradient rounded-xl p-6">
          <h3 class="text-lg font-bold text-white mb-4">1. Tipo de Torneo</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <button v-for="ft in formatTypes" :key="ft.value" @click="config.type = ft.value"
              :class="['p-4 rounded-xl border text-center transition-all', config.type === ft.value ? 'border-primary-500 bg-primary-500/10' : 'border-surface-700 hover:border-surface-500']">
              <span class="text-2xl">{{ ft.icon }}</span>
              <p class="text-sm font-medium mt-2" :class="config.type === ft.value ? 'text-primary-400' : 'text-white'">{{ ft.label }}</p>
              <p class="text-xs text-surface-500 mt-1">{{ ft.desc }}</p>
            </button>
          </div>
        </div>

        <!-- Step 2: Config -->
        <div class="card-gradient rounded-xl p-6">
          <h3 class="text-lg font-bold text-white mb-4">2. Configuración</h3>
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <input type="checkbox" v-model="config.is_round_trip" id="roundTrip" class="rounded border-surface-600" />
              <label for="roundTrip" class="text-sm text-surface-300">Ida y vuelta</label>
            </div>
            <div v-if="config.type === 'groups' || config.type === 'groups_knockout'" class="grid grid-cols-3 gap-4">
              <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Nº Grupos</label><input v-model.number="config.groups_count" type="number" min="1" max="16" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
              <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Equipos/Grupo</label><input v-model.number="config.teams_per_group" type="number" min="2" max="10" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
              <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Clasificados/Grupo</label><input v-model.number="config.qualified_per_group" type="number" min="1" max="8" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Fecha inicio</label><input v-model="config.start_date" type="date" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
              <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Hora partidos</label><input v-model="config.match_time" type="time" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
            </div>
          </div>
        </div>

        <!-- Step 3: Teams -->
        <div class="card-gradient rounded-xl p-6">
          <h3 class="text-lg font-bold text-white mb-4">3. Equipos Participantes <span class="text-sm text-surface-400 font-normal">({{ config.team_ids.length }} seleccionados)</span></h3>
          <div v-if="teams.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
            <button v-for="t in teams" :key="t.id" @click="toggleTeam(t.id)"
              :class="['p-3 rounded-lg border text-left transition-all flex items-center gap-2', config.team_ids.includes(t.id) ? 'border-primary-500 bg-primary-500/10' : 'border-surface-700 hover:border-surface-500']">
              <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" :style="{ backgroundColor: t.color || '#64748b' }">{{ t.name?.charAt(0) }}</div>
              <span class="text-sm truncate" :class="config.team_ids.includes(t.id) ? 'text-primary-400 font-medium' : 'text-surface-300'">{{ t.name }}</span>
            </button>
          </div>
          <p v-else class="text-surface-500 text-sm">No hay equipos en este campeonato.</p>
        </div>

        <!-- Validation errors -->
        <div v-if="validationErrors.length" class="p-4 rounded-lg bg-danger-500/10 border border-danger-500/20">
          <p class="text-sm font-medium text-danger-500 mb-2">Errores de validación:</p>
          <ul class="text-sm text-danger-500/80 list-disc list-inside space-y-1">
            <li v-for="(e, i) in validationErrors" :key="i">{{ e }}</li>
          </ul>
        </div>

        <!-- Generate button -->
        <div class="flex justify-end gap-3">
          <button @click="validateFormat" :disabled="generating" class="px-4 py-2.5 rounded-lg text-sm font-medium border border-surface-600 text-surface-300 hover:bg-surface-800 transition-colors">
            Validar
          </button>
          <button @click="generateFormat" :disabled="generating || !config.type || config.team_ids.length < 2" class="px-8 py-2.5 rounded-lg bg-primary-600 text-white text-sm font-bold hover:bg-primary-500 transition-colors disabled:opacity-50 shadow-lg">
            {{ generating ? 'Generando...' : '🚀 Generar Formato' }}
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi.js';
import { useNotificationStore } from '../../stores/notification.js';

const { get, post, loading } = useApi();
const notify = useNotificationStore();
const route = useRoute();
const router = useRouter();
const champId = route.params.championshipId;

const championship = ref(null);
const existingFormat = ref(null);
const teams = ref([]);
const generating = ref(false);
const validationErrors = ref([]);

const formatTypes = [
  { value: 'league', label: 'Liga', icon: '📊', desc: 'Todos contra todos' },
  { value: 'groups', label: 'Grupos', icon: '🏆', desc: 'Fase de grupos' },
  { value: 'knockout', label: 'Eliminación', icon: '⚡', desc: 'Eliminación directa' },
  { value: 'groups_knockout', label: 'Grupos + Elim.', icon: '🔥', desc: 'Grupos y luego llaves' },
];

const config = reactive({
  type: 'groups_knockout',
  is_round_trip: false,
  team_ids: [],
  groups_count: 2,
  teams_per_group: 4,
  qualified_per_group: 2,
  start_date: '',
  match_time: '10:00',
  venues: [],
});

function toggleTeam(id) {
  const idx = config.team_ids.indexOf(id);
  if (idx >= 0) config.team_ids.splice(idx, 1);
  else config.team_ids.push(id);
}

async function validateFormat() {
  validationErrors.value = [];
  try {
    const d = await post(`/campeonatos/${champId}/validar-formato`, config);
    if (d.valid) {
      notify.success('✓ Formato válido.');
    } else {
      validationErrors.value = d.errors || [];
    }
  } catch {}
}

async function generateFormat() {
  generating.value = true;
  validationErrors.value = [];
  try {
    const d = await post(`/campeonatos/${champId}/generar-formato`, config);
    existingFormat.value = d.data;
    notify.success('🚀 Formato generado correctamente. Partidos creados.');
  } catch (err) {
    if (err.response?.data?.errors) {
      validationErrors.value = err.response.data.errors;
    }
  }
  generating.value = false;
}

onMounted(async () => {
  try {
    const [c, f, t] = await Promise.all([
      get(`/campeonatos/${champId}`),
      get(`/campeonatos/${champId}/formato`).catch(() => ({ data: null })),
      get('/equipos', { championship_id: champId }),
    ]);
    championship.value = c.data || c;
    existingFormat.value = f.data;
    teams.value = t.data || [];
  } catch {}
});
</script>
