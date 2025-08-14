<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { toTypedSchema } from "@vee-validate/zod";
import { useForm } from "vee-validate";
import * as z from "zod";
import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";

const router = useRouter();

const formSchema = toTypedSchema(
  z.object({
    username: z.string().min(2, "Username must be at least 2 characters").max(30),
    email: z.string().email("Invalid email"),
    password: z.string().min(6, "Password must be at least 6 characters"),
  })
);

const form = useForm({
  validationSchema: formSchema,
});

const loading = ref(false);

const onSubmit = form.handleSubmit(async (values) => {
  loading.value = true;
  try {
    const response = await fetch("http://localhost:8000/api/register", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(values),
    });

    const data = await response.json();

    if (response.ok) {
      // Зберігаємо токен
      localStorage.setItem("api_token", data.token);

      
    } else {
      alert(data.message || "Registration error");
      console.error("❌ Registration failed:", data);
    }
  } catch (error) {
    console.error("🚨 Error:", error);
    alert("Server error. Try again.");
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="flex flex-col justify-center items-center h-screen">
    <div class="bg-[#312c32] min-h-[533px] sm:min-w-[400px] min-w-[350px] rounded-2xl flex flex-col items-center">
      <span class="text-2xl sm:text-3xl mt-5 opacity-100">Registration Form</span>

      <form
        @submit.prevent="onSubmit"
        class="bg-[#413b43] w-90 h-110 mt-3 rounded-xl flex flex-col justify-evenly items-center p-4"
      >
        <FormField v-slot="{ componentField }" name="username">
          <FormItem class="w-8/9">
            <FormLabel>Username</FormLabel>
            <FormControl>
              <Input type="text" placeholder="Write your username..." v-bind="componentField" />
            </FormControl>
            <FormMessage />
          </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="email">
          <FormItem class="w-8/9">
            <FormLabel>E-Mail</FormLabel>
            <FormControl>
              <Input type="email" placeholder="Write your email..." v-bind="componentField" />
            </FormControl>
            <FormMessage />
          </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="password">
          <FormItem class="w-8/9">
            <FormLabel>Password</FormLabel>
            <FormControl>
              <Input type="password" placeholder="Write your password..." v-bind="componentField" />
            </FormControl>
            <FormMessage />
          </FormItem>
        </FormField>

        <Button type="submit" class="button w-1/2 text-xl" size="lg" :disabled="loading">
          {{ loading ? "Loading..." : "Submit" }}
        </Button>
      </form>
    </div>
  </div>
</template>
