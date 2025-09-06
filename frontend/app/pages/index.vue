<script setup lang="ts">
import { Button } from "@/components/ui/button";
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
  enabledTransports: ["ws", "wss"],
  authEndpoint: "http://localhost:8088/broadcasting/auth",
  auth: {
    headers: {
      Authorization: `Bearer 3|EuZrmsI9cS1C4FMEjzgzFTLRUYvbFgbHESuTFOPE8cb39695`,
    },
  },
});

useEcho("chats.1", "MessageSent", (e) => {
  console.log(e);
});
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
