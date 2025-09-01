<template>
  <div class="flex h-screen">
    <Sidebar :chats="chats" />

    <!-- Права панель (поточний чат) -->
    <main class="flex-1 flex flex-col">
      <!-- Заголовок -->
      <header class="p-4 border-b border-gray-300">
        <h2 class="text-lg font-bold">
          {{ currentChat?.name || "Чат" }}
        </h2>
      </header>

      <!-- Повідомлення -->
      <div class="flex-1 p-4 overflow-y-auto space-y-2">
        <div
          v-for="(msg, i) in messages"
          :key="i"
          class="p-2 rounded-lg max-w-xs"
          :class="
            msg.me ? 'bg-blue-500 text-white ml-auto' : 'bg-gray-200 text-black'
          "
        >
          {{ msg.text }}
        </div>
      </div>

      <!-- Інпут -->
      <footer class="p-4 border-t border-gray-300 flex">
        <input
          v-model="newMessage"
          type="text"
          placeholder="Напишіть повідомлення..."
          class="flex-1 border rounded-lg px-3 py-2 mr-2"
          @keyup.enter="sendMessage"
        />
        <button
          @click="sendMessage"
          class="bg-blue-500 text-white px-4 py-2 rounded-lg"
        >
          Відправити
        </button>
      </footer>
    </main>
  </div>
</template>

<script setup>
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

function sendMessage() {
  if (newMessage.value.trim() === "") return;
  messages.value.push({ text: newMessage.value, me: true });
  newMessage.value = "";
}
</script>
