<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-white">Programación de Partidos</h2>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 mb-6">
      <select v-model="champFilter" @change="fetchMatches" class="px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
        <option value="">Todos los campeonatos</option>
        <option v-for="c in championships" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading && !matches.length" class="flex justify-center py-20">
      <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" />
    </div>

    <!-- Match List -->
    <div v-else class="space-y-4">
      <div v-for="m in matches" :key="m.id" class="card-gradient rounded-xl p-4">
        
        <!-- Teams and Status Header -->
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-surface-800">
          <div class="flex items-center gap-4">
            <span class="text-sm font-semibold text-white w-32 truncate text-right">{{ m.home_team?.name || 'TBD' }}</span>
            <span class="text-xs font-bold text-surface-500">VS</span>
            <span class="text-sm font-semibold text-white w-32 truncate">{{ m.away_team?.name || 'TBD' }}</span>
          </div>
          <span class="text-xs text-surface-400 bg-surface-800 px-2 py-1 rounded-md">{{ m.championship?.name }} - Fase: {{ m.phase?.name || 'N/A' }}</span>
        </div>

        <!-- Inline Edit Form -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div>
            <label class="block text-xs font-medium text-surface-400 mb-1">Fecha</label>
            <input v-model="m.edit_date" type="date" class="w-full px-3 py-2 text-sm rounded-lg bg-surface-900 border border-surface-700 text-white focus:outline-none focus:ring-1 focus:ring-primary-500" />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-400 mb-1">Hora</label>
            <input v-model="m.edit_time" type="time" class="w-full px-3 py-2 text-sm rounded-lg bg-surface-900 border border-surface-700 text-white focus:outline-none focus:ring-1 focus:ring-primary-500" />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-400 mb-1">Duración (minutos)</label>
            <input v-model.number="m.edit_duration" type="number" min="1" max="200" class="w-full px-3 py-2 text-sm rounded-lg bg-surface-900 border border-surface-700 text-white focus:outline-none focus:ring-1 focus:ring-primary-500" />
          </div>
          <div>
            <button @click="saveMatch(m)" :disabled="savingId === m.id" class="w-full px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-500 disabled:opacity-50 transition-colors">
              {{ savingId === m.id ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </div>

      </div>

      <p v-if="!matches.length" class="text-surface-500 text-center py-20">No hay partidos para mostrar.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
import { useNotificationStore } from '../../stores/notification.js';

const { get, put, loading } = useApi();
const notify = useNotificationStore();

const matches = ref([]);
const championships = ref([]);
const champFilter = ref('');
const savingId = ref(null);

async function fetchMatches() {
  try {
    const d = await get('/partidos', { championship_id: champFilter.value, status: 'scheduled' });
    const fetchedMatches = d.data || [];
    
    // Map to add temporary edit fields so we don't mess up original data until saved
    matches.value = fetchedMatches.map(m => ({
      ...m,
      edit_date: m.match_date || '',
      // Ensure time format is HH:MM for input[type="time"]
      edit_time: m.match_time ? m.match_time.substring(0, 5) : '', 
      edit_duration: m.match_duration || 30
    }));
  } catch {}
}

async function saveMatch(m) {
  savingId.value = m.id;
  try {
    await put(`/partidos/${m.id}`, {
      match_date: m.edit_date || null,
      match_time: m.edit_time || null,
      match_duration: m.edit_duration || null,
    });
    
    m.match_date = m.edit_date;
    m.match_time = m.edit_time;
    m.match_duration = m.edit_duration;
    
    notify.success('Programación actualizada.');
  } catch {}
  savingId.value = null;
}

onMounted(async () => {
  try {
    const d = await get('/campeonatos');
    championships.value = d.data || [];
  } catch {}
  
  await fetchMatches();
});
</script>
