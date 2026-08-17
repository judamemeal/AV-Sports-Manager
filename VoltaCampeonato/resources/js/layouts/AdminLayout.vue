<template>
  <div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-40 flex flex-col border-r border-surface-800 bg-surface-950 transition-all duration-300',
        sidebarOpen ? 'w-64' : 'w-16',
        'lg:relative',
      ]"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center px-4 border-b border-surface-800">
        <router-link to="/admin" class="flex items-center gap-3 overflow-hidden">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          </div>
          <span v-if="sidebarOpen" class="text-base font-bold text-white whitespace-nowrap">Volta<span class="text-primary-400">Admin</span></span>
        </router-link>
      </div>

      <!-- Nav links -->
      <nav class="flex-1 py-4 overflow-y-auto">
        <div class="space-y-1 px-2">
          <router-link v-for="item in sidebarItems" :key="item.to" :to="item.to"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-surface-400 hover:text-white hover:bg-surface-800 transition-all group"
            active-class="!text-primary-400 !bg-primary-500/10">
            <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
            <span v-if="sidebarOpen" class="whitespace-nowrap">{{ item.label }}</span>
          </router-link>
        </div>
      </nav>

      <!-- User section -->
      <div class="p-3 border-t border-surface-800">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
            {{ authStore.user?.name?.charAt(0) || 'A' }}
          </div>
          <div v-if="sidebarOpen" class="overflow-hidden">
            <p class="text-sm font-medium text-white truncate">{{ authStore.user?.name }}</p>
            <p class="text-xs text-surface-500 truncate">{{ authStore.user?.role_label }}</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Overlay for mobile -->
    <div v-if="sidebarOpen && isMobile" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden" />

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0" :class="sidebarOpen ? 'lg:ml-0' : ''">
      <!-- Top bar -->
      <header class="h-16 flex items-center justify-between px-4 sm:px-6 border-b border-surface-800 bg-surface-950/80 backdrop-blur-sm sticky top-0 z-20">
        <div class="flex items-center gap-4">
          <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-surface-400 hover:text-white hover:bg-surface-800 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <h1 class="text-lg font-semibold text-white hidden sm:block">{{ currentPageTitle }}</h1>
        </div>

        <div class="flex items-center gap-3">
          <router-link to="/" class="text-sm text-surface-400 hover:text-primary-400 transition-colors">
            Ver sitio público →
          </router-link>
          <button @click="handleLogout" class="px-3 py-1.5 rounded-lg text-xs font-medium text-surface-400 hover:text-white border border-surface-700 hover:border-surface-500 transition-colors">
            Cerrar Sesión
          </button>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 p-4 sm:p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, h } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const sidebarOpen = ref(true);
const isMobile = ref(false);

// Simple SVG icon components
const icon = (path) => ({
  render() {
    return h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: path })
    ]);
  }
});

const sidebarItems = [
  { to: '/admin', label: 'Dashboard', icon: icon('M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4') },
  { to: '/admin/campeonatos', label: 'Campeonatos', icon: icon('M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10') },
  { to: '/admin/equipos', label: 'Equipos', icon: icon('M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z') },
  { to: '/admin/jugadores', label: 'Jugadores', icon: icon('M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z') },
  { to: '/admin/partidos', label: 'Partidos', icon: icon('M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z') },
  { to: '/admin/posiciones', label: 'Posiciones', icon: icon('M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z') },
  { to: '/admin/usuarios', label: 'Usuarios', icon: icon('M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m9 5.197V21') },
];

const pageTitles = {
  'admin-dashboard': 'Dashboard',
  'admin-championships': 'Campeonatos',
  'admin-championship-create': 'Nuevo Campeonato',
  'admin-championship-edit': 'Editar Campeonato',
  'admin-teams': 'Equipos',
  'admin-team-create': 'Nuevo Equipo',
  'admin-team-edit': 'Editar Equipo',
  'admin-players': 'Jugadores',
  'admin-player-create': 'Nuevo Jugador',
  'admin-player-edit': 'Editar Jugador',
  'admin-matches': 'Partidos',
  'admin-match-play': 'Jugar Partido',
  'admin-format-builder': 'Constructor de Formato',
  'admin-standings': 'Tabla de Posiciones',
  'admin-users': 'Usuarios',
};

const currentPageTitle = computed(() => pageTitles[route.name] || 'Admin');

function checkMobile() {
  isMobile.value = window.innerWidth < 1024;
  if (isMobile.value) sidebarOpen.value = false;
}

async function handleLogout() {
  await authStore.logout();
  router.push('/');
}

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});
onUnmounted(() => window.removeEventListener('resize', checkMobile));
</script>
