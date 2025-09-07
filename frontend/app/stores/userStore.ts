import { defineStore } from "pinia";
import axios from "axios";
import { ref } from "vue";

export const useUserStore = defineStore("user", () => {
  const user = ref<any>(null);
  const loading = ref(true);

  const fetchUser = async (token: string) => {
    try {
      const response = await fetch("http://localhost:8000/api/user", {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${token}`,
        },
      });
      const data = await response.json();
      if (response.ok) {
        user.value = data;
        navigateTo("/chats");
      } else if (response.status === 401) {
        localStorage.removeItem("token");
        navigateTo("/login");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  };

  return { user, loading, fetchUser };
});
