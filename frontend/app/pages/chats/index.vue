<template>
  <div class="flex h-screen">
    <Sidebar :chats="chats" />

    <main class="flex-1 flex items-center justify-center">
      <p class="text-gray-500">Виберіть чат</p>
    </main>
  </div>
</template>

<script setup>
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
</script>
