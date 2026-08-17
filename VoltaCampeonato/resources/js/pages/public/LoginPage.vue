<template>
  <div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="w-full max-w-md animate-fade-in">
      <div class="card-gradient rounded-2xl p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
          <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center mx-auto shadow-lg">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          </div>
          <h1 class="text-2xl font-bold text-white mt-4">Iniciar Sesión</h1>
          <p class="text-sm text-surface-400 mt-1">Panel de administración</p>
        </div>

        <!-- Error -->
        <div v-if="errorMsg" class="mb-4 p-3 rounded-lg bg-danger-500/10 border border-danger-500/20 text-danger-500 text-sm">
          {{ errorMsg }}
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-surface-300 mb-1.5">Correo electrónico</label>
            <input v-model="form.email" type="email" required
              class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white placeholder-surface-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all"
              placeholder="admin@volta.edu" />
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-300 mb-1.5">Contraseña</label>
            <input v-model="form.password" type="password" required
              class="w-full px-4 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-white placeholder-surface-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all"
              placeholder="••••••••" />
          </div>
          <button type="submit" :disabled="loading"
            class="w-full py-2.5 rounded-lg bg-primary-600 text-white font-semibold hover:bg-primary-500 focus:ring-2 focus:ring-primary-500/50 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <div v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
            {{ loading ? 'Ingresando...' : 'Ingresar' }}
          </button>
        </form>

        <div class="mt-6 text-center">
          <router-link to="/" class="text-sm text-surface-400 hover:text-primary-400 transition-colors">
            ← Volver al inicio
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth.js';

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();
const loading = ref(false);
const errorMsg = ref('');

const form = reactive({ email: '', password: '' });

async function handleLogin() {
  loading.value = true;
  errorMsg.value = '';

  const result = await authStore.login(form.email, form.password);

  if (result.success) {
    const redirect = route.query.redirect || (authStore.isAdmin ? '/admin' : '/');
    router.push(redirect);
  } else {
    errorMsg.value = result.message;
  }

  loading.value = false;
}
</script>
