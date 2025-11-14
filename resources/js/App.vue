<template>
  <div v-if="hasError" class="error-container" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; text-align: center; padding: 20px;">
    <h1 style="color: #dc2626; font-size: 24px; margin-bottom: 16px;">{{ errorMessage }}</h1>
    <button
      @click="() => window.location.reload()"
      style="background-color: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px;"
    >
      Освежи страницу
    </button>
  </div>
  <router-view v-else />
</template>

<script setup>
import { onErrorCaptured, ref } from 'vue';

const hasError = ref(false);
const errorMessage = ref('');

// Note: Auth initialization is handled by router guard in router/index.js
// No need to call initAuth() here to avoid duplicate API calls

// Global error handler using Composition API
onErrorCaptured((err, instance, info) => {
  console.error('Глобална грешка:', err, info);
  hasError.value = true;
  errorMessage.value = 'Дошло је до неочекиване грешке. Молимо освежите страницу.';

  // Return false to prevent propagation
  return false;
});
</script>

<style>
</style>
