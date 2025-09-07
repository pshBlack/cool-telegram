<template>
  <aside
    class="w-1/4 p-4 bg-[#312c32] overflow-y-auto rounded-2xl my-4 ml-4 shadow-xl flex flex-col"
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
      v-if="filteredChats ? filteredChats.length > 0 : false"
      class="flex flex-col bg-[#4a444c] mt-4 rounded-md py-2 shadow-xl"
    >
      <li v-for="chat in filteredChats" :key="chat.chat_id" class="">
        <NuxtLink
          :to="`/chats/${chat.chat_id}`"
          class="flex items-center justify-between p-3 hover:bg-[#3b363e] transition"
        >
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-[#3a1016]"></div>

            <div class="flex flex-col">
              <span class="text-white font-semibold">
                {{ chat.otherUser.username }}
              </span>
              <span class="text-gray-400 text-sm truncate w-32"> </span>
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
    <ul
      v-else-if="users.length > 0"
      class="flex flex-col bg-[#4a444c] mt-4 rounded-md py-2 shadow-xl"
    >
      <li
        v-for="user in users"
        :key="user.user_id"
        class=""
        @click="
          () => {
            chatStore.createChat(user.username);
            text = '';
          }
        "
      >
        <NuxtLink
          class="flex items-center justify-between p-3 hover:bg-[#3b363e] transition"
        >
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-[#3a1016]"></div>

            <div class="flex flex-col">
              <span class="text-white font-semibold">
                {{ user.username }}
              </span>
              <span class="text-gray-400 text-sm truncate w-32"> </span>
            </div>
          </div>
        </NuxtLink>
      </li>
    </ul>

    <Button
      type="submit"
      class="button w-1/2 text-xl mt-auto flex justify-center self-center"
      size="lg"
      @click="logout"
      >Logout >
    </Button>
  </aside>
</template>
<script lang="ts" setup>
import { LogOut } from "lucide-vue-next";
import { Search } from "lucide-vue-next";
import axios from "axios";
import { useDebounceFn } from "@vueuse/core";
import Button from "../ui/button/Button.vue";
import { useChatsStore } from "~/stores/chatsStore";
const users = ref<any[]>([]);
const text = ref("");

const logout = async () => {
  localStorage.removeItem("token");
  localStorage.removeItem("username");
  await navigateTo("/login");
};

const findUser = useDebounceFn(async (newValue) => {
  if (text.value.length == 0) {
    users.value = [];
    return;
  }

  const { data } = await axios.get(
    `http://localhost:8000/api/users/search/${newValue}`,
    {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    }
  );
  users.value = data;
}, 300);
const chatStore = useChatsStore();

watch(text, async (newValue) => {
  findUser(newValue);
  users.value = [];
});
onMounted(async () => {
  await chatStore.fetchChats();
});

const filteredChats = computed(() =>
  chatStore.chats
    .map((chat) => {
      // знаходимо іншого користувача
      const otherUser = chat.users.find(
        (u: any) => u.username !== localStorage.getItem("user")
      );
      return {
        ...chat,
        otherUser, // додаємо іншого користувача в об’єкт чату
      };
    })
    .filter((chat) =>
      chat.otherUser.username.toLowerCase().includes(text.value.toLowerCase())
    )
);
</script>
