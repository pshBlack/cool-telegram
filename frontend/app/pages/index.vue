<script setup lang="ts">
import { Button } from "@/components/ui/button";
import { useEcho } from "@laravel/echo-vue";
import { configureEcho } from "@laravel/echo-vue";
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
