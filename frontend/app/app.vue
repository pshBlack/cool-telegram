<template>
  <div>
    <Toaster />
    <NuxtPage />
  </div>
</template>
<script setup lang="ts">
import { NuxtPage } from "#components";

import { Toaster } from "@/components/ui/sonner";
import "vue-sonner/style.css";
</script>
