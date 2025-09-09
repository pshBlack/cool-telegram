<template>
  <div class="flex h-screen">
    <Sidebar />

    <main class="flex-1 flex items-center justify-center">
      <p class="text-gray-500">Виберіть чат</p>
    </main>
  </div>
</template>

<script lang="ts" setup>
import { useUserStore } from "~/stores/userStore";
const userStore = useUserStore();

onMounted(async () => {
  await userStore.fetchUser();
});
</script>
