<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-white">Usuarios</h2>
      <button @click="showCreate = true" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-500 transition-colors">+ Nuevo Usuario</button>
    </div>
    <div v-if="loading" class="flex justify-center py-20"><div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" /></div>
    <div v-else class="card-gradient rounded-xl overflow-hidden">
      <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-surface-400 text-xs border-b border-surface-700">
        <th class="text-left py-3 px-4">Nombre</th><th class="text-left py-3 px-2">Email</th><th class="text-center py-3 px-2">Rol</th><th class="text-right py-3 px-4">Acciones</th>
      </tr></thead><tbody>
        <tr v-for="u in users" :key="u.id" class="border-b border-surface-800/50 hover:bg-surface-800/30">
          <td class="py-3 px-4"><div class="flex items-center gap-3"><div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold">{{ u.name?.charAt(0) }}</div><span class="text-white font-medium">{{ u.name }}</span></div></td>
          <td class="py-3 px-2 text-surface-300">{{ u.email }}</td>
          <td class="py-3 px-2 text-center"><span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium', u.role === 'admin' ? 'bg-primary-500/10 text-primary-400' : 'bg-surface-600/30 text-surface-400']">{{ u.role_label }}</span></td>
          <td class="py-3 px-4 text-right"><button @click="confirmDelete(u)" class="px-2 py-1 rounded text-xs text-danger-500 hover:bg-danger-500/10">Eliminar</button></td>
        </tr>
      </tbody></table></div>
      <p v-if="!users.length" class="text-surface-500 text-center py-10">No hay usuarios.</p>
    </div>

    <!-- Create User Modal -->
    <Teleport to="body">
      <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="showCreate = false" />
        <div class="relative card-gradient rounded-xl p-6 max-w-md w-full animate-fade-in">
          <h3 class="text-lg font-bold text-white mb-4">Nuevo Usuario</h3>
          <form @submit.prevent="createUser" class="space-y-4">
            <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Nombre *</label><input v-model="createForm.name" required class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
            <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Email *</label><input v-model="createForm.email" type="email" required class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
            <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Contraseña *</label><input v-model="createForm.password" type="password" required minlength="6" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50" /></div>
            <div><label class="block text-sm font-medium text-surface-300 mb-1.5">Rol</label><select v-model="createForm.role" class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/50"><option value="user">Usuario</option><option value="admin">Administrador</option></select></div>
            <div class="flex gap-3 justify-end pt-2">
              <button type="button" @click="showCreate = false" class="px-4 py-2 rounded-lg text-sm text-surface-300 hover:bg-surface-800">Cancelar</button>
              <button type="submit" :disabled="creating" class="px-6 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-500 disabled:opacity-50">{{ creating ? 'Creando...' : 'Crear' }}</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Delete Modal -->
    <Teleport to="body">
      <div v-if="showDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="showDelete = false" />
        <div class="relative card-gradient rounded-xl p-6 max-w-sm w-full animate-fade-in">
          <h3 class="text-lg font-bold text-white mb-2">¿Eliminar usuario?</h3>
          <p class="text-sm text-surface-400 mb-6">Se eliminará a "{{ deleteTarget?.name }}".</p>
          <div class="flex gap-3 justify-end">
            <button @click="showDelete = false" class="px-4 py-2 rounded-lg text-sm text-surface-300 hover:bg-surface-800">Cancelar</button>
            <button @click="handleDelete" :disabled="deleting" class="px-4 py-2 rounded-lg bg-danger-600 text-white text-sm font-medium hover:bg-danger-500 disabled:opacity-50">Eliminar</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useApi } from '../../composables/useApi.js';
import { useNotificationStore } from '../../stores/notification.js';
const { get, post, del, loading } = useApi();
const notify = useNotificationStore();
const users = ref([]); const showCreate = ref(false); const creating = ref(false);
const showDelete = ref(false); const deleteTarget = ref(null); const deleting = ref(false);
const createForm = reactive({ name: '', email: '', password: '', role: 'user' });
function confirmDelete(u) { deleteTarget.value = u; showDelete.value = true; }
async function handleDelete() { deleting.value = true; try { await del(`/usuarios/${deleteTarget.value.id}`); users.value = users.value.filter(u => u.id !== deleteTarget.value.id); notify.success('Usuario eliminado.'); showDelete.value = false; } catch {} deleting.value = false; }
async function createUser() { creating.value = true; try { const d = await post('/usuarios', createForm); users.value.push(d.data || d); createForm.name = ''; createForm.email = ''; createForm.password = ''; createForm.role = 'user'; showCreate.value = false; notify.success('Usuario creado.'); } catch {} creating.value = false; }
onMounted(async () => { try { const d = await get('/usuarios'); users.value = d.data || []; } catch {} });
</script>
