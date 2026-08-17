import { defineStore } from 'pinia';
import { ref } from 'vue';

let toastId = 0;

export const useNotificationStore = defineStore('notification', () => {
  const toasts = ref([]);

  function addToast(message, type = 'info', duration = 4000) {
    const id = ++toastId;
    toasts.value.push({ id, message, type });

    setTimeout(() => {
      toasts.value = toasts.value.filter(t => t.id !== id);
    }, duration);
  }

  function success(message) { addToast(message, 'success'); }
  function error(message) { addToast(message, 'error', 6000); }
  function warning(message) { addToast(message, 'warning'); }
  function info(message) { addToast(message, 'info'); }

  return { toasts, addToast, success, error, warning, info };
});
