<template>
  <div class="flex h-screen">
    <Sidebar />

    <!-- Права панель (поточний чат) -->
    <main
      class="flex-1 flex flex-col bg-[#312c32] m-4 rounded-2xl shadow-xl p-2"
    >
      <!-- Заголовок -->
      <header
        class="p-4 m-2 bg-[#4a444c] rounded-2xl flex justify-between items-center"
      >
        <h2 class="text-xl font-bold">
          {{ currentChat?.display_name || "Чат" }}
        </h2>
        <div class="flex gap-5">
          <UserPlus class="size-7 cursor-pointer" />
          <Phone class="size-7 cursor-pointer" />
          <Settings class="size-7 cursor-pointer" />
        </div>
      </header>

      <!-- Повідомлення -->
      <div ref="messagesContainer" class="flex-1 p-4 overflow-y-auto space-y-2">
        <div v-for="msg in messages" :key="msg.message_id">
          <ContextMenu asChild>
            <ContextMenuTrigger>
              <div
                class="p-3 rounded-lg break-words inline-flex min-w-[15%] max-w-fit items-end justify-between"
                :class="
                  msg.sender.username === name.value
                    ? 'bg-[#3a1016] text-[#EDEDEC]'
                    : 'bg-[#4a444d] text-[#EDEDEC]'
                "
              >
                {{ msg.content }}

                <div class="flex items-end">
                  <Transition name="time">
                    <span
                      class="text-base text-(--muted-foreground) ml-2 pl-2 translate-y-2 transition-all"
                      >{{ useDateFormat(msg.sent_at, "HH:mm") }}</span
                    >
                  </Transition>

                  <Check
                    v-if="
                      msg.status !== 'sent' &&
                      msg.sender.username === name.value
                    "
                    class="ml-2 size-4 translate-y-1.5 text-[#bbbabb] transition-all"
                  />
                  <Transition name="check">
                    <div
                      v-if="
                        msg.status === 'sent' &&
                        msg.sender.username === name.value
                      "
                      class="flex translate-y-1.5 transition-translate"
                    >
                      <Check class="size-4 translate-x-2 text-[#bbbabb]" />

                      <Check class="size-4 text-[#bbbabb]" />
                    </div>
                  </Transition>
                </div>
              </div>
            </ContextMenuTrigger>

            <ContextMenuContent>
              <ContextMenuItem @click="() => deleteMessage(msg.message_id)">
                <Trash2 class="mr-2" />
                Delete
              </ContextMenuItem>
              <ContextMenuItem @click="() => console.log(msg.message_id)">
                <InfoIcon class="mr-2" />Message ID</ContextMenuItem
              >
            </ContextMenuContent>
          </ContextMenu>
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
import { useChatsStore } from "~/store/chatsStore";
import { configureEcho } from "@laravel/echo-vue";
import { Trash2, InfoIcon, Check } from "lucide-vue-next";
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
import { useDateFormat } from "@vueuse/core";
const route = useRoute();
const chatsStore = useChatsStore();

const chatId = computed(() => Number(route.params.id)); // айді з URL
const currentChat = computed(() =>
  chatsStore.chats.find((chat) => chat.chat_id === chatId.value)
);
const messages = computed(() => chatsStore.chatMessages[chatId.value]);
const name = computed(() => useCookie("user"));
const newMessage = ref("");

const messagesContainer = ref(null);

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
}
const sendMessage = async () => {
  if (!newMessage.value) return;
  const tempMessage = newMessage.value;
  newMessage.value = "";
  scrollToBottom();
  const optimisticMessage = {
    message_id:
      chatsStore.chatMessages[chatId.value][
        chatsStore.chatMessages[chatId.value].length - 1
      ].message_id + 1,
    chat_id: chatId.value,
    content: tempMessage,
    sender: {
      avatar: null,
      username: useCookie("user").value,
    },
    status: "pending",
  };

  chatsStore.chatMessages[chatId.value].push(optimisticMessage);

  const realMessage = await chatsStore.sendMessageToChat(
    chatId.value,
    tempMessage
  );
  const index = chatsStore.chatMessages[chatId.value].findIndex(
    (m) => m.message_id === optimisticMessage.message_id
  );
  if (index !== -1) {
    chatsStore.chatMessages[chatId.value][index] = {
      ...realMessage.data,
      status: "sent",
    };
  }
  newMessage.value = "";
  scrollToBottom();
};

onMounted(async () => {
  if (!chatsStore.chats.length) {
    await chatsStore.fetchChats();
  }

  await chatsStore.getMessageFromChat(chatId.value);
  scrollToBottom();
});
import { useEcho } from "@laravel/echo-vue";
import axios from "axios";
configureEcho({
  broadcaster: "reverb",
  key: import.meta.env.VITE_REVERB_APP_KEY,
  cluster: "mt1",
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: Number(import.meta.env.VITE_REVERB_PORT),
  wssPort: Number(import.meta.env.VITE_REVERB_PORT),
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
  encrypted: true,
  enabledTransports: ["ws", "wss"],
  authEndpoint: "http://localhost:8000/broadcasting/auth",
  authorizer: (channel, options) => {
    return {
      authorize: (socketId, callback) => {
        axios
          .post(
            "http://localhost:8000/broadcasting/auth",
            {
              socket_id: socketId,

              channel_name: channel.name,
            },
            {
              withCredentials: true,
            }
          )

          .then((response) => {
            callback(null, response.data);
          })

          .catch((error) => {
            callback(error);
          });
      },
    };
  },
});

const deleteMessage = async (messageId) => {
  const messages = chatsStore.chatMessages[chatId.value] || [];
  const index = messages.findIndex((m) => m.message_id === messageId);

  messages.splice(index, 1);
  await chatsStore.deleteMessageFetch(messageId);
};

const messageSent = useEcho(`chat.${chatId.value}`, ".message.sent", (e) => {
  const messages = chatsStore.chatMessages[chatId.value] || [];

  // якщо такого message_id ще немає — пушимо
  const exists = messages.some((m) => m.message_id === e.message_id);
  if (exists || e.sender.username === useCookie("user").value) return;

  messages.push(e);
  scrollToBottom();
});
const messageDelete = useEcho(
  `chat.${chatId.value}`,
  ".message.deleted",
  (e) => {
    const messages = chatsStore.chatMessages[chatId.value] || [];
    const index = messages.findIndex((m) => m.message_id === e.message_id);

    if (e.username === useCookie("user").value) return;
    messages.splice(index, 1);
  }
);
</script>
