import { defineStore } from "pinia";
import axios from "axios";
import { ref } from "vue";
import type { Chat } from "~/constants/interfaces";
import { toast } from "vue-sonner";

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
      const { data, status } = await axios.get(
        "http://localhost:8000/api/chats",
        {
          headers: {
            Accept: "application/json",
            "X-XSRF-TOKEN": `${useCookie("XSRF-TOKEN").value}`,
          },
          withCredentials: true,
        }
      );
      chats.value = data;
      if (status === 401) {
        toast.error("Unauthorized");
        useCookie("user").value = "";
        navigateTo("/login");
      }
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
    await fetchChats();
    return data;
  };

  const deleteMessageFetch = async (messageId: number) => {
    await callCookie();
    const { data, status } = await axios.delete(
      `http://localhost:8000/api/messages/${messageId}`,
      {
        headers: {
          Accept: "application/json",
          "X-XSRF-TOKEN": `${useCookie("XSRF-TOKEN").value}`,
        },
        withCredentials: true,
      }
    );
    if (status === 200) {
      toast.success("Message deleted");
      console.log(data);
    }
  };

  const getMessageFromChat = async (chatId: number) => {
    const { data } = await axios.get(
      `http://localhost:8000/api/chats/${chatId}/messages`,
      { withCredentials: true }
    );
    chatMessages[chatId] = data.map((msg: any) => ({
      ...msg,
      status: "sent", // позначимо свої
    }));
    return data;
  };

  const deleteChat = async (chatId: number) => {
    await callCookie();
    const { data, status } = await axios.delete(
      `http://localhost:8000/api/chats/${chatId}`,
      {
        headers: {
          Accept: "application/json",
          "X-XSRF-TOKEN": `${useCookie("XSRF-TOKEN").value}`,
        },
        withCredentials: true,
      }
    );
    if (status === 200) {
      toast.success("Chat deleted");
      console.log(data);
    }
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
    deleteMessageFetch,
    deleteChat,
  };
});
