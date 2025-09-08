import { defineStore } from "pinia";
import axios from "axios";
import { ref } from "vue";
import { toast } from "vue-sonner";
import type { User, Token } from "~/constants/interfaces";
interface loginSchema {
  email?: string;
  password?: string;
}
interface registerSchema {
  username?: string;
  email?: string;
  password?: string;
}

export const useUserStore = defineStore("user", () => {
  const user = ref<User | null>(null);
  const loading = ref(true);

  const fetchUser = async (token: string): Promise<void> => {
    try {
      const response = await fetch("http://localhost:8000/api/user", {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${token}`,
        },
      });
      const data: User = await response.json();
      if (response.ok) {
        user.value = data;
        navigateTo("/chats");
      } else if (response.status === 401) {
        localStorage.removeItem("token");
        navigateTo("/login");
      }
    } catch (error) {
      console.error("Error:", error);
      toast.error("Something went wrong");
    }
  };
  const fetchRegister = async (values: registerSchema): Promise<void> => {
    try {
      const response = await fetch("http://localhost:8000/api/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(values),
      });
      const data: User & Token = await response.json();
      if (response.ok) {
        toast.success("Registration successful");
        localStorage.setItem("token", data.token);
        await navigateTo("/chats");
      } else {
        toast.error("Registration failed");
      }
    } catch (error) {
      console.error("Error:", error);
      toast.error("Something went wrong");
    }
  };

  const fetchLogin = async (values: loginSchema): Promise<void> => {
    try {
      const response = await fetch("http://localhost:8000/api/login", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(values),
      });
      const data: { message: string } & User & Token = await response.json();
      if (response.ok) {
        toast.success("Login successful");
        localStorage.setItem("token", data.token);
        localStorage.setItem("user", data.user.username);
        console.log(data);
        await navigateTo("/chats");
      } else {
        toast.error("Login failed");
      }
    } catch (error) {
      console.error("Error:", error);
      toast.error("Something went wrong");
    }
  };

  return { user, loading, fetchUser, fetchRegister, fetchLogin };
});
