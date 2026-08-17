<template>
  <div class="max-w-2xl animate-fade-in">
    <router-link to="/admin/campeonatos" class="text-sm text-surface-400 hover:text-primary-400 mb-4 inline-block">← Campeonatos</router-link>
    <h2 class="text-2xl font-bold text-white mb-6">{{ isEdit ? 'Editar Campeonato' : 'Nuevo Campeonato' }}</h2>
    <form @submit.prevent="handleSubmit" class="card-gradient rounded-xl p-6 space-y-5">
      <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Nombre *</label><input v-model="form.name" required class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white placeholder-surface-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50 transition-all" placeholder="Campeonato Intercursos de Fútbol 2026" /></div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Deporte *</label><input v-model="form.sport" required class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" placeholder="Fútbol" /></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Año *</label><input v-model.number="form.year" type="number" required min="2020" max="2050" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Categoría *</label><input v-model="form.category" required class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" placeholder="Bachillerato" /></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Nivel de Curso</label><input v-model="form.course_level" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" placeholder="Bachillerato" /></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Fecha Inicio</label><input v-model="form.start_date" type="date" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Fecha Fin</label><input v-model="form.end_date" type="date" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
      </div>
      <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Estado</label>
        <select v-model="form.status" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50"><option value="upcoming">Próximo</option><option value="active">Activo</option><option value="finished">Finalizado</option></select>
      </div>
      <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Descripción</label><textarea v-model="form.description" rows="3" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white placeholder-surface-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50 resize-none" /></div>
      <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Reglamento</label><textarea v-model="form.regulations" rows="4" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white placeholder-surface-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50 resize-none" /></div>
      <div class="flex gap-3 justify-end pt-2">
        <router-link to="/admin/campeonatos" class="px-4 py-2.5 rounded-lg text-sm text-surface-300 hover:bg-surface-800 transition-colors">Cancelar</router-link>
        <button type="submit" :disabled="submitting" class="px-6 py-2.5 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-500 transition-colors disabled:opacity-50">{{ submitting ? 'Guardando...' : (isEdit ? 'Actualizar' : 'Crear') }}</button>
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
const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const submitting = ref(false);
const form = reactive({ name: '', year: 2026, sport: '', category: '', course_level: '', start_date: '', end_date: '', description: '', regulations: '', status: 'upcoming' });
onMounted(async () => { if (isEdit.value) { try { const d = await get(`/campeonatos/${route.params.id}`); const c = d.data || d; Object.keys(form).forEach(k => { if (c[k] !== undefined && c[k] !== null) form[k] = c[k]; }); } catch {} } });
async function handleSubmit() {
  submitting.value = true;
  try {
    if (isEdit.value) { await put(`/campeonatos/${route.params.id}`, form); notify.success('Campeonato actualizado.'); }
    else { await post('/campeonatos', form); notify.success('Campeonato creado.'); }
    router.push('/admin/campeonatos');
  } catch {} submitting.value = false;
}
</script>
