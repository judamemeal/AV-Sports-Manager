import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('auth_token') || null);

  const isAuthenticated = computed(() => !!user.value);
  const isAdmin = computed(() => user.value?.role === 'admin');

  // Set axios default header
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  }

  async function login(email, password) {
    try {
      const { data } = await axios.post('/api/login', { email, password });
      user.value = data.user;
      token.value = data.token;
      localStorage.setItem('auth_token', data.token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`;
      return { success: true };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Error al iniciar sesión',
      };
    }
  }

  async function logout() {
    try {
      await axios.post('/api/logout');
    } catch {
      // Ignore errors on logout
    }
    user.value = null;
    token.value = null;
    localStorage.removeItem('auth_token');
    delete axios.defaults.headers.common['Authorization'];
  }

  async function fetchUser() {
    if (!token.value) return;
    try {
      const { data } = await axios.get('/api/user');
      user.value = data.data;
    } catch {
      user.value = null;
      token.value = null;
      localStorage.removeItem('auth_token');
      delete axios.defaults.headers.common['Authorization'];
    }
  }

  return { user, token, isAuthenticated, isAdmin, login, logout, fetchUser };
});
