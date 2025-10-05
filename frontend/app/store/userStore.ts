import { defineStore } from "pinia";
import axios from "axios";
import { ref } from "vue";
import { toast } from "vue-sonner";

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
  const loading = ref(true);

  const fetchUser = async (): Promise<void> => {
    try {
      const { data, status } = await axios.get(
        "http://localhost:8000/api/user",
        {
          headers: {
            Accept: "application/json",
            "X-XSRF-TOKEN": `${useCookie("XSRF-TOKEN").value}`,
          },
          withCredentials: true,
        }
      );

      if (status === 200) {
        const user = useCookie("user");
        user.value = data.user.username;

        navigateTo("/chats");
      }
    } catch (error: any) {
      if (error.response?.status === 401) {
        toast.error("Unauthorized");
        useCookie("user").value = "";
        useCookie("XSRF-TOKEN").value = "";
        useCookie("laravel-session").value = "";
        navigateTo("/login");
      } else {
        console.error("Error:", error);
        toast.error("Something went wrong");
      }
    }
  };
  const fetchRegister = async (values: registerSchema): Promise<void> => {
    await callCookie();
    try {
      const response = await axios.post(
        "http://localhost:8000/api/register",
        values,
        {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-XSRF-TOKEN": `${useCookie("XSRF-TOKEN").value}`,
          },
          withCredentials: true,
        }
      );

      if (response.status === 200) {
        toast.success("Registration successful");
        const token = useCookie("token");
        const user = useCookie("user");
        token.value = response.data.token;
        user.value = response.data.user.username;
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
      await callCookie();
      const response = await axios.post(
        "http://localhost:8000/api/login",
        values,
        {
          headers: {
            "Content-Type": "application/json",
            accept: "application/json",
            "X-XSRF-TOKEN": `${useCookie("XSRF-TOKEN").value}`,
          },
          withCredentials: true,
        }
      );
      if (response.status === 200) {
        toast.success("Login successful");
        const user = useCookie("user");
        user.value = response.data.user.username;
        console.log(response.data);
        await navigateTo("/chats");
      } else {
        toast.error("Login failed");
      }
    } catch (error) {
      console.error("Error:", error);
      toast.error("Something went wrong");
    }
  };
  const callCookie = async () => {
    await axios.get("http://localhost:8000/sanctum/csrf-cookie", {
      withCredentials: true,
    });
  };

  const logout = async () => {
    await callCookie();
    await axios.post("http://localhost:8000/api/logout");
  };

  return { loading, fetchUser, fetchRegister, fetchLogin };
});
