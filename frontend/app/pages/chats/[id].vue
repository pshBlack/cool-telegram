<template>
  <div class="flex h-screen">
    <Sidebar :chats="chats" />

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
          v-for="(msg, i) in messages"
          :key="i"
          class="p-4 rounded-lg max-w-xs"
          :class="
            msg.me
              ? 'bg-[#3a1016] text-[#EDEDEC] ml-auto'
              : 'bg-[#4a444d] text-[#EDEDEC]'
          "
        >
          {{ msg.text }}
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
const route = useRoute();

let chats = [
  {
    id: 1,
    name: "Cool Jobless",
    lastMessage: "Please let me work...",
    unread: 1,
  },
  {
    id: 2,
    name: "ool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
  {
    id: 3,
    name: "l Jobless",
    lastMessage: "Please let me work...",
    unread: 3,
  },
  {
    id: 4,
    name: "aool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
  {
    id: 4,
    name: "Cool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
  {
    id: 4,
    name: "bool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
  {
    id: 4,
    name: "Cool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
  {
    id: 4,
    name: "Cool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
  {
    id: 4,
    name: "Cool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
  {
    id: 4,
    name: "Cool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
  {
    id: 4,
    name: "Cool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
  {
    id: 4,
    name: "Cool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
  {
    id: 4,
    name: "Cool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
  {
    id: 4,
    name: "Cool Jobless",
    lastMessage: "Please let me work...",
    unread: 0,
  },
];

const currentChat = chats.find((c) => c.id == route.params.id);

const messages = ref([
  { text: "Привіт 👋", me: false },
  { text: "Як справи?", me: false },
  { text: "Все добре, працюю над Nuxt 🚀", me: true },
]);

const newMessage = ref("");

const messagesContainer = ref(null);

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
}

function sendMessage() {
  if (newMessage.value.trim() === "") return;
  messages.value.push({ text: newMessage.value, me: true });
  newMessage.value = "";
  scrollToBottom();
}

onMounted(() => {
  scrollToBottom();
});
</script>
