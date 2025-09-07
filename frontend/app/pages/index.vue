<script setup lang="ts">
import { Button } from "@/components/ui/button";
import { useEcho } from "@laravel/echo-vue";
import { configureEcho } from "@laravel/echo-vue";
import { navigateTo } from '#app';

configureEcho({
  broadcaster: "reverb",
  appId: 663070,
  key: "vdh8pyexp2aph77b7bkh",
  wsHost: "localhost",
  wsPort: 8088,
  wssPort: 8088,
  forceTLS: false,
  enabledTransports: ["ws", "wss"],
  authEndpoint: "http://localhost:8000/broadcasting/auth",
  auth: {
    headers: {
      Authorization: `Bearer 6|ZVXIUxVyt0b8lVVx7VyXLw3RQ8tFaJLZfcglUXpdccdeca91`,
    },
  },
});

const echo = useEcho();

// Слухаємо приватний канал через Reverb
echo.private("chats.1")
  .listen("MessageSent", (e) => {
    console.log("Новий меседж:", e);
  });

  const goToLogin = async () => {
  await navigateTo('/login');
};  

</script>

<template>
  <div class="flex flex-col justify-center items-center h-screen">
    <h1 class="text-7xl">Cool Chat</h1>
    <Button
      size="lg"
      class="text-2xl mt-5 button active:bg-[#302830]/45"
      @click="
        async () => {
          await navigateTo('/login');
        }
      "
    >
      Start</Button
    >
  </div>
</template>
