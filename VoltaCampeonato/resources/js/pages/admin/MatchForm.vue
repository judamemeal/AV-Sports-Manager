<template>
  <div class="max-w-2xl animate-fade-in">
    <router-link to="/admin/partidos" class="text-sm text-surface-400 hover:text-primary-400 mb-4 inline-block">← Partidos</router-link>
    <h2 class="text-2xl font-bold text-white mb-6">{{ isEdit ? 'Editar Partido' : 'Nuevo Partido' }}</h2>
    <form @submit.prevent="handleSubmit" class="card-gradient rounded-xl p-6 space-y-5">
      <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Campeonato *</label>
        <select v-model="form.championship_id" required @change="loadTeams" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50"><option value="">Seleccionar...</option><option v-for="c in championships" :key="c.id" :value="c.id">{{ c.name }}</option></select></div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Equipo Local *</label><select v-model="form.home_team_id" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50"><option value="">Seleccionar...</option><option v-for="t in teams" :key="t.id" :value="t.id">{{ t.name }}</option></select></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Equipo Visitante *</label><select v-model="form.away_team_id" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50"><option value="">Seleccionar...</option><option v-for="t in teams" :key="t.id" :value="t.id" :disabled="t.id === form.home_team_id">{{ t.name }}</option></select></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Fecha</label><input v-model="form.match_date" type="date" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Hora</label><input v-model="form.match_time" type="time" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
      </div>
      <div class="grid grid-cols-1 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Duración (minutos)</label><input v-model.number="form.match_duration" type="number" min="1" max="200" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Lugar</label><input v-model="form.venue" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" placeholder="Cancha principal" /></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Árbitro</label><input v-model="form.referee" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
      </div>
      <div class="flex gap-3 justify-end pt-2">
        <router-link to="/admin/partidos" class="px-4 py-2.5 rounded-lg text-sm text-surface-300 hover:bg-surface-800">Cancelar</router-link>
        <button type="submit" :disabled="submitting" class="px-6 py-2.5 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-500 disabled:opacity-50">{{ submitting ? 'Guardando...' : (isEdit ? 'Actualizar' : 'Crear') }}</button>
      </div>
    </form>
  </div>
</template>
<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi.js';
import { useNotificationStore } from '../../stores/notification.js';
const { get, post, put } = useApi();
const notify = useNotificationStore();
const route = useRoute(); const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const submitting = ref(false); const championships = ref([]); const teams = ref([]);
const form = reactive({ championship_id: '', home_team_id: '', away_team_id: '', match_date: '', match_time: '', venue: '', referee: '', match_duration: 30 });
async function loadTeams() { if (!form.championship_id) { teams.value = []; return; } try { const d = await get('/equipos', { championship_id: form.championship_id }); teams.value = d.data || []; } catch {} }
onMounted(async () => {
  try { const d = await get('/campeonatos'); championships.value = d.data || []; } catch {}
  if (isEdit.value) { try { const d = await get(`/partidos/${route.params.id}`); const m = d.data || d; Object.keys(form).forEach(k => { if (m[k] !== undefined && m[k] !== null) form[k] = m[k]; }); await loadTeams(); } catch {} }
});
async function handleSubmit() { submitting.value = true; try { if (isEdit.value) { await put(`/partidos/${route.params.id}`, form); notify.success('Partido actualizado.'); } else { await post('/partidos', form); notify.success('Partido creado.'); } router.push('/admin/partidos'); } catch {} submitting.value = false; }
</script>
