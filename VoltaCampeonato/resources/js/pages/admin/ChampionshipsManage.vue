<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-white">Campeonatos</h2>
      <router-link to="/admin/campeonatos/crear" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-500 transition-colors">+ Nuevo Campeonato</router-link>
    </div>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <div v-else class="card-gradient rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead><tr class="text-surface-400 text-xs border-b border-surface-700">
            <th class="text-left py-3 px-4">Nombre</th><th class="text-left py-3 px-2">Deporte</th><th class="text-left py-3 px-2">Categoría</th><th class="text-center py-3 px-2">Año</th><th class="text-center py-3 px-2">Estado</th><th class="text-center py-3 px-2">Equipos</th><th class="text-right py-3 px-4">Acciones</th>
          </tr></thead>
          <tbody>
            <tr v-for="c in championships" :key="c.id" class="border-b border-surface-800/50 hover:bg-surface-800/30 transition-colors">
              <td class="py-3 px-4 text-white font-medium">{{ c.name }}</td>
              <td class="py-3 px-2 text-surface-300">{{ c.sport }}</td>
              <td class="py-3 px-2 text-surface-300">{{ c.category }}</td>
              <td class="py-3 px-2 text-center text-surface-300">{{ c.year }}</td>
              <td class="py-3 px-2 text-center"><span :class="['px-2 py-0.5 rounded-full text-xs font-medium', c.status === 'active' ? 'bg-green-500/10 text-green-400' : c.status === 'upcoming' ? 'bg-blue-500/10 text-blue-400' : 'bg-surface-600/30 text-surface-400']">{{ c.status_label }}</span></td>
              <td class="py-3 px-2 text-center text-surface-300">{{ c.teams_count || 0 }}</td>
              <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <router-link :to="`/admin/formatos/${c.id}`" class="px-2 py-1 rounded text-xs text-accent-400 hover:bg-accent-500/10 transition-colors">Formato</router-link>
                  <router-link :to="`/admin/campeonatos/${c.id}/editar`" class="px-2 py-1 rounded text-xs text-primary-400 hover:bg-primary-500/10 transition-colors">Editar</router-link>
                  <button @click="confirmDelete(c)" class="px-2 py-1 rounded text-xs text-danger-500 hover:bg-danger-500/10 transition-colors">Eliminar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!championships.length" class="text-surface-500 text-center py-10">No hay campeonatos creados.</p>
    </div>
    <!-- Delete Modal -->
    <Teleport to="body">
      <div v-if="showDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="showDelete = false" />
        <div class="relative card-gradient rounded-xl p-6 max-w-sm w-full animate-fade-in">
          <h3 class="text-lg font-bold text-white mb-2">¿Eliminar campeonato?</h3>
          <p class="text-sm text-surface-400 mb-6">Se eliminará "{{ deleteTarget?.name }}" y todos sus datos asociados. Esta acción no se puede deshacer.</p>
          <div class="flex gap-3 justify-end">
            <button @click="showDelete = false" class="px-4 py-2 rounded-lg text-sm text-surface-300 hover:bg-surface-800 transition-colors">Cancelar</button>
            <button @click="handleDelete" :disabled="deleting" class="px-4 py-2 rounded-lg bg-danger-600 text-white text-sm font-medium hover:bg-danger-500 transition-colors disabled:opacity-50">{{ deleting ? 'Eliminando...' : 'Eliminar' }}</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
import { useNotificationStore } from '../../stores/notification.js';
const { get, del, loading } = useApi();
const notify = useNotificationStore();
const championships = ref([]);
const showDelete = ref(false);
const deleteTarget = ref(null);
const deleting = ref(false);
function confirmDelete(c) { deleteTarget.value = c; showDelete.value = true; }
async function handleDelete() { deleting.value = true; try { await del(`/campeonatos/${deleteTarget.value.id}`); championships.value = championships.value.filter(c => c.id !== deleteTarget.value.id); notify.success('Campeonato eliminado.'); showDelete.value = false; } catch {} deleting.value = false; }
onMounted(async () => { try { const d = await get('/campeonatos'); championships.value = d.data || []; } catch {} });
</script>
