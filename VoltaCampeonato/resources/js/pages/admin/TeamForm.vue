<template>
  <div class="max-w-2xl animate-fade-in">
    <router-link to="/admin/equipos" class="text-sm text-surface-400 hover:text-primary-400 mb-4 inline-block">← Equipos</router-link>
    <h2 class="text-2xl font-bold text-white mb-6">{{ isEdit ? 'Editar Equipo' : 'Nuevo Equipo' }}</h2>
    <form @submit.prevent="handleSubmit" class="card-gradient rounded-xl p-6 space-y-5">
      <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Campeonatos (Ctrl+Click para varios)</label>
        <select multiple v-model="form.championship_ids" class="w-full h-24 px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50"><option v-for="c in championships" :key="c.id" :value="c.id">{{ c.name }}</option></select></div>
      <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Nombre del Equipo *</label><input v-model="form.name" required class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" placeholder="Los Titanes" /></div>
      <div class="grid grid-cols-3 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Curso</label><input v-model="form.course" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" placeholder="3ro Bach." /></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Paralelo</label><input v-model="form.parallel" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" placeholder="A" /></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Categoría</label><input v-model="form.category" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" placeholder="Bachillerato" /></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Color</label><div class="flex items-center gap-3"><input v-model="form.color" type="color" class="w-10 h-10 rounded-lg border border-surface-700 cursor-pointer" /><span class="text-surface-400 text-sm">{{ form.color }}</span></div></div>
        <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Capitán</label><input v-model="form.captain_name" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
      </div>
      <div class="flex gap-3 justify-end pt-2">
        <router-link to="/admin/equipos" class="px-4 py-2.5 rounded-lg text-sm text-surface-300 hover:bg-surface-800">Cancelar</router-link>
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
const submitting = ref(false);
const championships = ref([]);
const form = reactive({ championship_ids: [], name: '', course: '', parallel: '', category: '', color: '#10b981', captain_name: '' });
onMounted(async () => {
  try { const d = await get('/campeonatos'); championships.value = d.data || []; } catch {}
  if (isEdit.value) { 
    try { 
      const d = await get(`/equipos/${route.params.id}`); 
      const t = d.data || d; 
      Object.keys(form).forEach(k => { 
        if (k === 'championship_ids') {
          form.championship_ids = (t.championships || []).map(c => c.id);
        } else if (t[k] !== undefined && t[k] !== null) {
          form[k] = t[k]; 
        }
      }); 
    } catch {} 
  }
});
async function handleSubmit() { submitting.value = true; try { if (isEdit.value) { await put(`/equipos/${route.params.id}`, form); notify.success('Equipo actualizado.'); } else { await post('/equipos', form); notify.success('Equipo creado.'); } router.push('/admin/equipos'); } catch {} submitting.value = false; }
</script>
