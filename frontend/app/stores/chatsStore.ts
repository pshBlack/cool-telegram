import { defineStore } from "pinia";
import axios from "axios";
import { ref } from "vue";
import type { NuxtBuilder } from "nuxt/schema";

export const useChatsStore = defineStore("chats", () => {
  const chats = ref<any[]>([]);
  const loading = ref(true);
  const chatMessages = reactive<Record<number, any[]>>({});
  const fetchChats = async () => {
    try {
      const { data } = await axios.get("http://localhost:8000/api/chats", {
        headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
      });
      chats.value = data;
    } finally {
      loading.value = false;
    }
  };
  const createChat = async (identifier: string) => {
    const { data } = await axios.post(
      "http://localhost:8000/api/chats",
      { identifier },
      { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
    );
    // після створення нового чату оновлюємо список
    await fetchChats();
    return data;
  };

  const sendMessageToChat = async (chatId: number, message: string) => {
    const { data } = await axios.post(
      `http://localhost:8000/api/chats/${chatId}/messages`,
      { content: message },
      { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
    );
    if (!chatMessages[chatId]) chatMessages[chatId] = [];
    chatMessages[chatId]?.push({
      ...data,
      me: true, // щоб UI показував, що це твоє повідомлення
    });
    console.log(chatMessages);
    return data;
  };

  const getMessageFromChat = async (chatId: number) => {
    const { data } = await axios.get(
      `http://localhost:8000/api/chats/${chatId}/messages`,
      { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
    );
    chatMessages[chatId] = data.map((msg: any) => ({
      ...msg,
      me: msg.sender_id === Number(localStorage.getItem("user_id")), // позначимо свої
    }));
    return data;
  };
  return {
    chats,
    loading,
    chatMessages,
    fetchChats,
    createChat,
    sendMessageToChat,
    getMessageFromChat,
  };
});
