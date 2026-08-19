<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="glass sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <router-link to="/" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-lg group-hover:shadow-primary-500/30 transition-shadow">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="text-lg font-bold text-white hidden sm:block">Volta<span class="text-primary-400">Campeonato</span></span>
          </router-link>

          <!-- Desktop Nav -->
          <div class="hidden md:flex items-center gap-1">
            <router-link v-for="link in navLinks" :key="link.to" :to="link.to"
              class="px-3 py-2 rounded-lg text-sm font-medium text-surface-300 hover:text-white hover:bg-surface-800 transition-all"
              active-class="!text-primary-400 !bg-primary-500/10">
              {{ link.label }}
            </router-link>
          </div>

          <!-- Right side -->
          <div class="flex items-center gap-3">
            <router-link v-if="authStore.isAdmin" to="/admin"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary-600 text-white hover:bg-primary-500 transition-colors">
              Panel Admin
            </router-link>
            <router-link v-else-if="!authStore.isAuthenticated" to="/login"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-surface-600 text-surface-300 hover:text-white hover:border-surface-400 transition-colors">
              Iniciar Sesión
            </router-link>
            <button v-else @click="handleLogout"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold text-surface-400 hover:text-white transition-colors">
              Salir
            </button>

            <!-- Mobile menu button -->
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg text-surface-400 hover:text-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Nav -->
      <transition name="slide">
        <div v-if="mobileOpen" class="md:hidden border-t border-surface-800 pb-4">
          <router-link v-for="link in navLinks" :key="link.to" :to="link.to"
            @click="mobileOpen = false"
            class="block px-6 py-3 text-sm text-surface-300 hover:text-white hover:bg-surface-800 transition-colors"
            active-class="!text-primary-400 !bg-primary-500/10">
            {{ link.label }}
          </router-link>
        </div>
      </transition>
    </nav>

    <!-- Page content -->
    <main class="flex-1">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="border-t border-surface-800 mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-2 text-surface-500 text-sm">
            <div class="w-6 h-6 rounded bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
              <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            VoltaCampeonato © {{ new Date().getFullYear() }}
          </div>
          <p class="text-surface-600 text-xs">Sistema de Campeonatos Deportivos</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';

const authStore = useAuthStore();
const router = useRouter();
const mobileOpen = ref(false);

const navLinks = [
  { to: '/', label: 'Inicio' },
  { to: '/equipos', label: 'Equipos' },
  { to: '/jugadores', label: 'Jugadores' },
  { to: '/calendario', label: 'Calendario' },
];

async function handleLogout() {
  await authStore.logout();
  router.push('/');
}
</script>
