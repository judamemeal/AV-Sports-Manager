<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-white">Partidos</h2>
      <router-link to="/admin/partidos/crear" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-500 transition-colors">+ Nuevo Partido</router-link>
    </div>
    <div class="flex flex-wrap gap-3 mb-6">
      <select v-model="champFilter" @change="fetchMatches" class="px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
        <option value="">Todos los campeonatos</option><option v-for="c in championships" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
      <select v-model="statusFilter" @change="fetchMatches" class="px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50">
        <option value="">Todos los estados</option><option value="scheduled">Programados</option><option value="in_progress">En Juego</option><option value="finished">Finalizados</option>
      </select>
    </div>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <div v-else class="space-y-3">
      <div v-for="m in matches" :key="m.id" :class="['card-gradient rounded-xl p-4 flex items-center justify-between', m.status === 'in_progress' ? 'glow-live' : '']">
        <div class="flex items-center gap-3 flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-1 min-w-0">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" :style="{ backgroundColor: m.home_team?.color || '#64748b' }">{{ m.home_team?.name?.charAt(0) || '?' }}</div>
            <span class="text-sm text-white truncate">{{ m.home_team?.name || 'TBD' }}</span>
          </div>
          <div class="text-center px-3 flex-shrink-0">
            <p v-if="m.status === 'finished' || m.status === 'in_progress'" class="text-lg font-bold text-white">{{ m.home_score }} - {{ m.away_score }}</p>
            <p v-else class="text-sm font-bold text-surface-500">VS</p>
            <p class="text-xs text-surface-500">{{ m.match_date || '' }}</p>
          </div>
          <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
            <span class="text-sm text-white truncate">{{ m.away_team?.name || 'TBD' }}</span>
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" :style="{ backgroundColor: m.away_team?.color || '#64748b' }">{{ m.away_team?.name?.charAt(0) || '?' }}</div>
          </div>
        </div>
        <div class="flex items-center gap-2 ml-4 flex-shrink-0">
          <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusClass(m.status)]">{{ m.status_label }}</span>
          <router-link v-if="m.status === 'scheduled'" :to="`/admin/partidos/${m.id}/jugar`" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-green-600 text-white hover:bg-green-500">▶ Jugar</router-link>
          <router-link v-if="m.status === 'in_progress'" :to="`/admin/partidos/${m.id}/jugar`" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-green-600 text-white hover:bg-green-500 badge-live">⚡ En Vivo</router-link>
          <router-link :to="`/admin/partidos/${m.id}/editar`" class="px-2 py-1 rounded text-xs text-primary-400 hover:bg-primary-500/10">Editar</router-link>
          <button @click="confirmDelete(m)" class="px-2 py-1 rounded text-xs text-danger-500 hover:bg-danger-500/10">✕</button>
        </div>
      </div>
      <p v-if="!matches.length" class="text-surface-500 text-center py-20">No hay partidos.</p>
    </div>
    <Teleport to="body"><div v-if="showDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4"><div class="absolute inset-0 bg-black/60" @click="showDelete = false" /><div class="relative card-gradient rounded-xl p-6 max-w-sm w-full animate-fade-in"><h3 class="text-lg font-bold text-white mb-4">¿Eliminar partido?</h3><div class="flex gap-3 justify-end"><button @click="showDelete = false" class="px-4 py-2 rounded-lg text-sm text-surface-300 hover:bg-surface-800">Cancelar</button><button @click="handleDelete" :disabled="deleting" class="px-4 py-2 rounded-lg bg-danger-600 text-white text-sm font-medium hover:bg-danger-500 disabled:opacity-50">Eliminar</button></div></div></div></Teleport>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
import { useNotificationStore } from '../../stores/notification.js';
const { get, del, loading } = useApi();
const notify = useNotificationStore();
const matches = ref([]); const championships = ref([]); const champFilter = ref(''); const statusFilter = ref('');
const showDelete = ref(false); const deleteTarget = ref(null); const deleting = ref(false);
function statusClass(s) { return { scheduled: 'bg-blue-500/10 text-blue-400', in_progress: 'bg-green-500/10 text-green-400', finished: 'bg-surface-600/30 text-surface-400' }[s] || ''; }
function confirmDelete(m) { deleteTarget.value = m; showDelete.value = true; }
async function handleDelete() { deleting.value = true; try { await del(`/partidos/${deleteTarget.value.id}`); matches.value = matches.value.filter(m => m.id !== deleteTarget.value.id); notify.success('Partido eliminado.'); showDelete.value = false; } catch {} deleting.value = false; }
async function fetchMatches() { try { const d = await get('/partidos', { championship_id: champFilter.value, status: statusFilter.value }); matches.value = d.data || []; } catch {} }
onMounted(async () => { try { const d = await get('/campeonatos'); championships.value = d.data || []; } catch {} await fetchMatches(); });
</script>
