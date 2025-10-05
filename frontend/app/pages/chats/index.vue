<template>
  <div class="flex h-screen">
    <Sidebar />

    <main class="flex-1 flex items-center justify-center">
      <p class="text-gray-500">Виберіть чат</p>
    </main>
  </div>
</template>

<script lang="ts" setup>
import { useUserStore } from "~/store/userStore";
import { useChatsStore } from "~/store/chatsStore";

const chatStore = useChatsStore();
const userStore = useUserStore();

onMounted(async () => {
  await userStore.fetchUser();
  await chatStore.fetchChats();
});
</script>
