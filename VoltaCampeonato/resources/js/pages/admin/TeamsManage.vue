<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-white">Equipos</h2>
      <router-link to="/admin/equipos/crear" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-500 transition-colors">+ Nuevo Equipo</router-link>
    </div>
    <div class="flex gap-3 mb-6">
      <input v-model="search" @input="fetchTeams" placeholder="Buscar..." class="flex-1 max-w-sm px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white placeholder-surface-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50" />
      <select v-model="champFilter" @change="fetchTeams" class="px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
        <option value="">Todos los campeonatos</option>
        <option v-for="c in championships" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <div v-else class="card-gradient rounded-xl overflow-hidden">
      <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-surface-400 text-xs border-b border-surface-700">
        <th class="text-left py-3 px-4">Equipo</th><th class="text-left py-3 px-2">Curso</th><th class="text-left py-3 px-2">Campeonato</th><th class="text-center py-3 px-2">Jugadores</th><th class="text-center py-3 px-2">Estado</th><th class="text-right py-3 px-4">Acciones</th>
      </tr></thead><tbody>
        <tr v-for="t in teams" :key="t.id" class="border-b border-surface-800/50 hover:bg-surface-800/30 transition-colors">
          <td class="py-3 px-4"><div class="flex items-center gap-3"><div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: t.color || '#64748b' }">{{ t.name?.charAt(0) }}</div><span class="text-white font-medium">{{ t.name }}</span></div></td>
          <td class="py-3 px-2 text-surface-300">{{ t.course }} {{ t.parallel }}</td>
          <td class="py-3 px-2 text-surface-400 text-xs">{{ t.championship?.name }}</td>
          <td class="py-3 px-2 text-center text-surface-300">{{ t.players_count || 0 }}</td>
          <td class="py-3 px-2 text-center"><span :class="['px-2 py-0.5 rounded-full text-xs', t.is_active ? 'bg-green-500/10 text-green-400' : 'bg-surface-600/30 text-surface-400']">{{ t.is_active ? 'Activo' : 'Inactivo' }}</span></td>
          <td class="py-3 px-4 text-right"><div class="flex items-center justify-end gap-2">
            <router-link :to="`/admin/equipos/${t.id}/editar`" class="px-2 py-1 rounded text-xs text-primary-400 hover:bg-primary-500/10">Editar</router-link>
            <button @click="confirmDelete(t)" class="px-2 py-1 rounded text-xs text-danger-500 hover:bg-danger-500/10">Eliminar</button>
          </div></td>
        </tr>
      </tbody></table></div>
      <p v-if="!teams.length" class="text-surface-500 text-center py-10">No hay equipos.</p>
    </div>
    <Teleport to="body"><div v-if="showDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4"><div class="absolute inset-0 bg-black/60" @click="showDelete = false" /><div class="relative card-gradient rounded-xl p-6 max-w-sm w-full animate-fade-in"><h3 class="text-lg font-bold text-white mb-2">¿Eliminar equipo?</h3><p class="text-sm text-surface-400 mb-6">Se eliminará "{{ deleteTarget?.name }}".</p><div class="flex gap-3 justify-end"><button @click="showDelete = false" class="px-4 py-2 rounded-lg text-sm text-surface-300 hover:bg-surface-800">Cancelar</button><button @click="handleDelete" :disabled="deleting" class="px-4 py-2 rounded-lg bg-danger-600 text-white text-sm font-medium hover:bg-danger-500 disabled:opacity-50">{{ deleting ? 'Eliminando...' : 'Eliminar' }}</button></div></div></div></Teleport>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
import { useNotificationStore } from '../../stores/notification.js';
const { get, del, loading } = useApi();
const notify = useNotificationStore();
const teams = ref([]); const championships = ref([]); const search = ref(''); const champFilter = ref('');
const showDelete = ref(false); const deleteTarget = ref(null); const deleting = ref(false);
let timeout;
function confirmDelete(t) { deleteTarget.value = t; showDelete.value = true; }
async function handleDelete() { deleting.value = true; try { await del(`/equipos/${deleteTarget.value.id}`); teams.value = teams.value.filter(t => t.id !== deleteTarget.value.id); notify.success('Equipo eliminado.'); showDelete.value = false; } catch {} deleting.value = false; }
async function fetchTeams() { clearTimeout(timeout); timeout = setTimeout(async () => { try { const d = await get('/equipos', { search: search.value, championship_id: champFilter.value }); teams.value = d.data || []; } catch {} }, 300); }
onMounted(async () => { try { const d = await get('/campeonatos'); championships.value = d.data || []; } catch {} await fetchTeams(); });
</script>
