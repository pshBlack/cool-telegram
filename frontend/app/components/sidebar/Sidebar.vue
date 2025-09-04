<template>
  <aside
    class="w-1/4 p-4 bg-[#312c32] overflow-y-auto rounded-2xl my-4 ml-4 shadow-xl"
  >
    <div class="relative w-full max-w-sm items-center">
      <span
        class="absolute end-0 inset-y-0 flex items-center justify-center pr-4"
      >
        <Search class="size-5 text-muted-foreground" />
      </span>
      <Input
        id="search"
        type="text"
        placeholder="Search for a new friend..."
        class="pl-4 py-4 shadow-2xl placeholder:text-lg rounded-md"
        v-model="text"
      />
    </div>

    <ul
      v-if="filteredChats.length > 0"
      class="flex flex-col bg-[#4a444c] mt-4 rounded-md py-2 shadow-xl"
    >
      <li v-for="chat in filteredChats" :key="chat.id" class="">
        <NuxtLink
          :to="`/chats/${chat.id}`"
          class="flex items-center justify-between p-3 hover:bg-[#3b363e] transition"
        >
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-[#3a1016]"></div>

            <div class="flex flex-col">
              <span class="text-white font-semibold">
                {{ chat.name }}
              </span>
              <span class="text-gray-400 text-sm truncate w-32">
                {{ chat.lastMessage }}
              </span>
            </div>
          </div>

          <div
            v-if="chat.unread > 0"
            class="bg-red-800 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full"
          >
            {{ chat.unread }}
          </div>
        </NuxtLink>
      </li>
    </ul>
  </aside>
</template>
<script lang="ts" setup>
const { chats } = defineProps<{
  chats: any[];
}>();

const text = ref("");

const filteredChats = computed(() => {
  return chats.filter((chat) =>
    chat.name.toLowerCase().includes(text.value.toLowerCase())
  );
});

import { Search } from "lucide-vue-next";
</script>
