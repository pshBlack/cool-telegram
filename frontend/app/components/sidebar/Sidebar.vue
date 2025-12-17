<template>
  <aside
    class="w-1/4 p-4 bg-[#312c32] rounded-2xl my-4 ml-4 shadow-xl flex flex-col"
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
      v-if="filteredChats && filteredChats.length > 0"
      class="flex flex-col bg-[#4a444c] mt-4 rounded-md py-2 shadow-xl overflow-y-auto"
    >
      <li v-for="chat in filteredChats" :key="chat.chat_id">
        <ContextMenu>
          <ContextMenuTrigger asChild>
            <NuxtLink
              :to="`/chats/${chat.chat_id}`"
              class="flex items-center justify-between p-3 hover:bg-[#3b363e] transition"
            >
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-[#3a1016]"></div>
                <div class="flex flex-col">
                  <span class="text-white">{{ chat.otherUser.username }}</span>
                  <span class="text-gray-400 text-sm truncate w-32">
                    {{ chat.messages[0]?.content }}
                  </span>
                </div>
              </div>
              <div v-if="chat.messages.length > 0">
                <span class="text-xs text-gray-400 ml-2">
                  {{ useDateFormat(chat.messages[0]?.sent_at, "HH:mm") }}
                </span>
              </div>
            </NuxtLink>
          </ContextMenuTrigger>

          <ContextMenuContent>
            <ContextMenuItem @click="showCharId(chat.chat_id)">
              <InfoIcon class="mr-2" />Chat ID
            </ContextMenuItem>
            <ContextMenuItem @click="chatStore.deleteChat(chat.chat_id)">
              <Trash class="mr-2" />Delete Chat
            </ContextMenuItem>
          </ContextMenuContent>
        </ContextMenu>
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
    <div class="mt-auto">
      <Profile />
    </div>
  </aside>
</template>
<script lang="ts" setup>
import { LogOut } from "lucide-vue-next";
import { Search, InfoIcon, Trash } from "lucide-vue-next";
import axios from "axios";
import { useDebounceFn } from "@vueuse/core";
import Button from "../ui/button/Button.vue";
import { useChatsStore } from "~/store/chatsStore";
import { useDateFormat } from "@vueuse/core";
import {
  ContextMenu,
  ContextMenuCheckboxItem,
  ContextMenuContent,
  ContextMenuItem,
  ContextMenuLabel,
  ContextMenuRadioGroup,
  ContextMenuRadioItem,
  ContextMenuSeparator,
  ContextMenuShortcut,
  ContextMenuSub,
  ContextMenuSubContent,
  ContextMenuSubTrigger,
  ContextMenuTrigger,
} from "@/components/ui/context-menu";
const users = ref<any[]>([]);
const text = ref("");

// const logout = async () => {
//   await axios.post("http://localhost:8000/api/logout");
//   await navigateTo("/login");
// };

const callCookie = async () => {
  await axios.get("http://localhost:8000/sanctum/csrf-cookie", {
    withCredentials: true,
  });
};

const findUser = useDebounceFn(async (newValue) => {
  if (text.value.length == 0) {
    users.value = [];
    return;
  }
  callCookie();
  const { data } = await axios.get(
    `http://localhost:8000/api/users/search/${newValue}`,
    {
      headers: {
        Accept: "application/json",
        "X-XSRF-TOKEN": `${useCookie("XSRF-TOKEN").value}`,
      },
      withCredentials: true,
    }
  );
  users.value = data;
}, 200);
const chatStore = useChatsStore();
watch(text, async (newValue) => {
  findUser(newValue);
  users.value = [];
});
const showCharId = (chatId: number) => {
  console.log(chatId);
};
const filteredChats = computed(() =>
  chatStore.chats
    .map((chat) => {
      // знаходимо іншого користувача
      const otherUser: any = chat.users.find(
        (u: any) => u.username !== useCookie("user").value
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
onMounted(async () => {});
</script>
