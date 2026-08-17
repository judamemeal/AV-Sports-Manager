import { ref } from 'vue';
import axios from 'axios';
import { useNotificationStore } from '../stores/notification.js';

export function useApi() {
  const loading = ref(false);
  const error = ref(null);

  async function get(url, params = {}) {
    loading.value = true;
    error.value = null;
    try {
      const { data } = await axios.get(`/api${url}`, { params });
      return data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function post(url, payload = {}, config = {}) {
    loading.value = true;
    error.value = null;
    try {
      const { data } = await axios.post(`/api${url}`, payload, config);
      return data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al enviar datos';
      const notify = useNotificationStore();
      if (err.response?.status === 422 && err.response?.data?.errors) {
        const messages = Object.values(err.response.data.errors).flat();
        notify.error(messages.join('. '));
      } else {
        notify.error(error.value);
      }
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function put(url, payload = {}) {
    loading.value = true;
    error.value = null;
    try {
      const { data } = await axios.put(`/api${url}`, payload);
      return data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar';
      const notify = useNotificationStore();
      notify.error(error.value);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function del(url) {
    loading.value = true;
    error.value = null;
    try {
      const { data } = await axios.delete(`/api${url}`);
      return data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar';
      const notify = useNotificationStore();
      notify.error(error.value);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  return { loading, error, get, post, put, del };
}
