<template>
  <router-view />
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { onMounted } from 'vue';

const authStore = useAuthStore();

onMounted(async () => {
  await authStore.initAuth();
});
</script>

<style>
</style>
