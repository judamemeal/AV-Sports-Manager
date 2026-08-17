<template>
  <router-view v-slot="{ Component, route }">
    <transition name="page" mode="out-in">
      <component :is="Component" :key="route.path" />
    </transition>
  </router-view>

  <!-- Toast notifications -->
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2">
      <transition-group name="fade">
        <div
          v-for="toast in notificationStore.toasts"
          :key="toast.id"
          :class="[
            'px-4 py-3 rounded-lg shadow-xl text-sm font-medium max-w-sm animate-slide-up',
            toast.type === 'success' ? 'bg-primary-600 text-white' : '',
            toast.type === 'error' ? 'bg-danger-600 text-white' : '',
            toast.type === 'warning' ? 'bg-warning-600 text-white' : '',
            toast.type === 'info' ? 'bg-accent-600 text-white' : '',
          ]"
        >
          {{ toast.message }}
        </div>
      </transition-group>
    </div>
  </Teleport>
</template>

<script setup>
import { useNotificationStore } from './stores/notification.js';

const notificationStore = useNotificationStore();
</script>
