<template>
  <div class="flex h-screen">
    <Sidebar />

    <!-- Права панель (поточний чат) -->
    <main
      class="flex-1 flex flex-col bg-[#312c32] m-4 rounded-2xl shadow-xl p-2"
    >
      <!-- Заголовок -->
      <header
        class="p-4 m-2 bg-[#4a444c] rounded-2xl flex justify-between items-center"
      >
        <h2 class="text-xl font-bold">
          {{ currentChat?.name || "Чат" }}
        </h2>
        <div class="flex gap-5">
          <UserPlus class="size-7 cursor-pointer" />
          <Phone class="size-7 cursor-pointer" />
          <Settings class="size-7 cursor-pointer" />
        </div>
      </header>

      <!-- Повідомлення -->
      <div ref="messagesContainer" class="flex-1 p-4 overflow-y-auto space-y-2">
        <div
          v-for="msg in messages"
          :key="msg.message_id"
          class="p-4 rounded-lg max-w-xs"
          :class="
            msg.me
              ? 'bg-[#3a1016] text-[#EDEDEC] ml-auto'
              : 'bg-[#4a444d] text-[#EDEDEC]'
          "
        >
          {{ msg.content }}
        </div>
      </div>

      <!-- Інпут -->
      <footer class="p-2 m-2 bg-[#4a444c] rounded-2xl flex">
        <Input
          v-model="newMessage"
          type="text"
          placeholder="Write your message..."
          class="flex-1 rounded-md px-3 py-5 mr-2 text-2xl placeholder:text-lg"
          @keyup.enter="sendMessage"
        />
      </footer>
    </main>
  </div>
</template>

<script setup>
import { UserPlus, Phone, Settings } from "lucide-vue-next";
import { useChatsStore } from "@/stores/chatsStore";
import { setActivePinia, createPinia } from "pinia";

setActivePinia(createPinia());
const route = useRoute();
const chatsStore = useChatsStore();
const chatId = computed(() => route.params.id); // айді з URL
const currentChat = ref(null);
const messages = computed(() => chatsStore.chatMessages[chatId.value] || []);

const newMessage = ref("");

const messagesContainer = ref(null);

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
}
const sendMessage = async () => {
  if (!newMessage.value) return;
  console.log(
    await chatsStore.sendMessageToChat(chatId.value, newMessage.value)
  );

  newMessage.value = "";
  scrollToBottom();
};

onMounted(async () => {
  currentChat.value = chatId.value;
  scrollToBottom();
  await chatsStore.getMessageFromChat(chatId.value);
});
import { useEcho } from "@laravel/echo-vue";
import { configureEcho } from "@laravel/echo-vue";

configureEcho({
  broadcaster: "reverb",
  key: import.meta.env.VITE_REVERB_APP_KEY,
  cluster: "mt1",
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: Number(import.meta.env.VITE_REVERB_PORT),
  wssPort: Number(import.meta.env.VITE_REVERB_PORT),
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
  encrypted: true,
  enabledTransports: ["ws", "wss"],
  authEndpoint: "http://localhost:8000/broadcasting/auth",
  auth: {
    headers: {
      Authorization: `Bearer 28|QYPwxNCO3CpFyCbfAbKb19gkfrsTWc9UEVVyr67l6a7f53f1`,
    },
  },
});

useEcho(`chat.${chatId.value}`, "MessageSent", (e) => {
  console.log(e);
});
</script>
