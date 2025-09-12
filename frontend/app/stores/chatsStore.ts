import { defineStore } from "pinia";
import axios from "axios";
import { ref } from "vue";
import type { Chat } from "~/constants/interfaces";

export const useChatsStore = defineStore("chats", () => {
  const chats = ref<Chat[]>([]);
  const loading = ref(true);
  const chatMessages = reactive<Record<number, any[]>>({});

  const callCookie = async () => {
    await axios.get("http://localhost:8000/sanctum/csrf-cookie", {
      withCredentials: true,
    });
  };

  const fetchChats = async (): Promise<void> => {
    try {
      const { data } = await axios.get("http://localhost:8000/api/chats", {
        headers: {
          Accept: "application/json",
          "X-XSRF-TOKEN": `${useCookie("XSRF-TOKEN").value}`,
        },
        withCredentials: true,
      });
      chats.value = data;
    } finally {
      loading.value = false;
    }
  };
  const createChat = async (identifier: string): Promise<Chat> => {
    await callCookie();
    const { data } = await axios.post(
      "http://localhost:8000/api/chats",
      { identifier },
      {
        headers: {
          Accept: "application/json",
          "X-XSRF-TOKEN": `${useCookie("XSRF-TOKEN").value}`,
        },

        withCredentials: true,
      }
    );
    await navigateTo(`/chats/${data.chat.chat_id}`);
    await fetchChats();
    return data;
  };

  const sendMessageToChat = async (chatId: number, message: string) => {
    await callCookie();
    const { data } = await axios.post(
      `http://localhost:8000/api/chats/${chatId}/messages`,
      { content: message },
      {
        headers: {
          Accept: "application/json",
          "X-XSRF-TOKEN": `${useCookie("XSRF-TOKEN").value}`,
        },
        withCredentials: true,
      }
    );

    return data;
  };

  const getMessageFromChat = async (chatId: number) => {
    const { data } = await axios.get(
      `http://localhost:8000/api/chats/${chatId}/messages`,
      { withCredentials: true }
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
    callCookie,
  };
});
