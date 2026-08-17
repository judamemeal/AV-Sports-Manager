<template>
  <div class="max-w-2xl animate-fade-in">
    <router-link to="/admin/jugadores" class="text-sm text-surface-400 hover:text-primary-400 mb-4 inline-block">← Jugadores</router-link>
    <h2 class="text-2xl font-bold text-white mb-6">{{ isEdit ? 'Editar Jugador' : 'Nuevo Jugador' }}</h2>
    <form @submit.prevent="handleSubmit" class="card-gradient rounded-xl p-6 space-y-5">
      <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Equipo *</label>
        <select v-model="form.team_id" required class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50"><option value="">Seleccionar...</option><option v-for="t in teams" :key="t.id" :value="t.id">{{ t.name }} ({{ t.championship?.name }})</option></select></div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Nombre *</label><input v-model="form.first_name" required class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Apellido *</label><input v-model="form.last_name" required class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
      </div>
      <div class="grid grid-cols-3 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Dorsal</label><input v-model.number="form.jersey_number" type="number" min="1" max="99" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Posición</label><select v-model="form.position" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50"><option value="">Seleccionar</option><option value="goalkeeper">Portero</option><option value="defender">Defensa</option><option value="midfielder">Mediocampista</option><option value="forward">Delantero</option></select></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Fecha Nac.</label><input v-model="form.birth_date" type="date" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Curso</label><input v-model="form.course" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Paralelo</label><input v-model="form.parallel" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
      </div>
      <div class="flex gap-3 justify-end pt-2">
        <router-link to="/admin/jugadores" class="px-4 py-2.5 rounded-lg text-sm text-surface-300 hover:bg-surface-800">Cancelar</router-link>
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
const submitting = ref(false); const teams = ref([]);
const form = reactive({ team_id: '', first_name: '', last_name: '', jersey_number: null, position: '', course: '', parallel: '', birth_date: '' });
onMounted(async () => {
  try { const d = await get('/equipos'); teams.value = d.data || []; } catch {}
  if (isEdit.value) { try { const d = await get(`/jugadores/${route.params.id}`); const p = d.data || d; Object.keys(form).forEach(k => { if (p[k] !== undefined && p[k] !== null) form[k] = p[k]; }); } catch {} }
});
async function handleSubmit() { submitting.value = true; try { if (isEdit.value) { await put(`/jugadores/${route.params.id}`, form); notify.success('Jugador actualizado.'); } else { await post('/jugadores', form); notify.success('Jugador creado.'); } router.push('/admin/jugadores'); } catch {} submitting.value = false; }
</script>
