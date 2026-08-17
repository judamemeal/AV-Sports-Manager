<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-white">Jugadores</h2>
      <router-link to="/admin/jugadores/crear" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-500 transition-colors">+ Nuevo Jugador</router-link>
    </div>
    <div class="flex flex-wrap gap-3 mb-6">
      <input v-model="search" @input="fetchPlayers" placeholder="Buscar jugador..." class="flex-1 min-w-[200px] px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white placeholder-surface-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50" />
      <select v-model="teamFilter" @change="fetchPlayers" class="px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
        <option value="">Todos los equipos</option>
        <option v-for="t in allTeams" :key="t.id" :value="t.id">{{ t.name }}</option>
      </select>
    </div>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <div v-else class="card-gradient rounded-xl overflow-hidden">
      <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-surface-400 text-xs border-b border-surface-700">
        <th class="text-left py-3 px-4">#</th><th class="text-left py-3 px-2">Jugador</th><th class="text-left py-3 px-2">Posición</th><th class="text-left py-3 px-2">Equipo</th><th class="text-center py-3 px-2">Goles</th><th class="text-center py-3 px-2">🟨</th><th class="text-center py-3 px-2">🟥</th><th class="text-right py-3 px-4">Acciones</th>
      </tr></thead><tbody>
        <tr v-for="p in players" :key="p.id" class="border-b border-surface-800/50 hover:bg-surface-800/30 transition-colors">
          <td class="py-3 px-4 font-bold text-surface-400">{{ p.jersey_number }}</td>
          <td class="py-3 px-2 text-white font-medium">{{ p.full_name }}</td>
          <td class="py-3 px-2 text-surface-300">{{ p.position_label }}</td>
          <td class="py-3 px-2"><div class="flex items-center gap-2"><div class="w-5 h-5 rounded-full flex-shrink-0" :style="{ backgroundColor: p.team?.color || '#64748b' }" /><span class="text-surface-300 text-xs">{{ p.team?.name }}</span></div></td>
          <td class="py-3 px-2 text-center text-primary-400 font-bold">{{ p.goals_count || 0 }}</td>
          <td class="py-3 px-2 text-center text-yellow-400">{{ p.yellow_cards_count || 0 }}</td>
          <td class="py-3 px-2 text-center text-danger-500">{{ p.red_cards_count || 0 }}</td>
          <td class="py-3 px-4 text-right"><div class="flex items-center justify-end gap-2">
            <router-link :to="`/admin/jugadores/${p.id}/editar`" class="px-2 py-1 rounded text-xs text-primary-400 hover:bg-primary-500/10">Editar</router-link>
            <button @click="confirmDelete(p)" class="px-2 py-1 rounded text-xs text-danger-500 hover:bg-danger-500/10">Eliminar</button>
          </div></td>
        </tr>
      </tbody></table></div>
      <p v-if="!players.length" class="text-surface-500 text-center py-10">No hay jugadores.</p>
    </div>
    <Teleport to="body"><div v-if="showDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4"><div class="absolute inset-0 bg-black/60" @click="showDelete = false" /><div class="relative card-gradient rounded-xl p-6 max-w-sm w-full animate-fade-in"><h3 class="text-lg font-bold text-white mb-2">¿Eliminar jugador?</h3><p class="text-sm text-surface-400 mb-6">Se eliminará a "{{ deleteTarget?.full_name }}".</p><div class="flex gap-3 justify-end"><button @click="showDelete = false" class="px-4 py-2 rounded-lg text-sm text-surface-300 hover:bg-surface-800">Cancelar</button><button @click="handleDelete" :disabled="deleting" class="px-4 py-2 rounded-lg bg-danger-600 text-white text-sm font-medium hover:bg-danger-500 disabled:opacity-50">Eliminar</button></div></div></div></Teleport>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
import { useNotificationStore } from '../../stores/notification.js';
const { get, del, loading } = useApi();
const notify = useNotificationStore();
const players = ref([]); const allTeams = ref([]); const search = ref(''); const teamFilter = ref('');
const showDelete = ref(false); const deleteTarget = ref(null); const deleting = ref(false);
let timeout;
function confirmDelete(p) { deleteTarget.value = p; showDelete.value = true; }
async function handleDelete() { deleting.value = true; try { await del(`/jugadores/${deleteTarget.value.id}`); players.value = players.value.filter(p => p.id !== deleteTarget.value.id); notify.success('Jugador eliminado.'); showDelete.value = false; } catch {} deleting.value = false; }
async function fetchPlayers() { clearTimeout(timeout); timeout = setTimeout(async () => { try { const d = await get('/jugadores', { search: search.value, team_id: teamFilter.value }); players.value = d.data || []; } catch {} }, 300); }
onMounted(async () => { try { const d = await get('/equipos'); allTeams.value = d.data || []; } catch {} await fetchPlayers(); });
</script>
