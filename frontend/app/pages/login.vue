<script setup lang="ts">
import { Input } from "@/components/ui/input";
import { toTypedSchema } from "@vee-validate/zod";
import { useForm } from "vee-validate";
import * as z from "zod";
import {
  FormControl,
  Form,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { toast } from "vue-sonner";
import { onMounted } from "vue";
import Cookies from "universal-cookie";

const cookies = new Cookies(null, { path: "/" });

const formSchema = toTypedSchema(
  z.object({
    email: z.string().email(),
    password: z.string().min(8),
  })
);

const form = useForm({
  validationSchema: formSchema,
});

const getUser = async (token: string) => {
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
    console.log(data);
  } catch (error) {
    console.error("Error:", error);
  }
};

const onSubmit = form.handleSubmit(async (values) => {
  try {
    const response = await fetch("http://localhost:8000/api/login", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(values),
    });
    const data = await response.json();
    if (response.ok) {
      toast.success("Login successful");
      localStorage.setItem("token", data.token);
    } else {
      toast.error("Login failed");
      console.log(data);
    }
  } catch (error) {
    console.error("Error:", error);
  }
});

onMounted(() => {
  const token = localStorage.getItem("token");
  if (token) {
    getUser(token);
  }
});
</script>

<template>
  <div class="flex flex-col justify-center items-center h-screen">
    <div
      class="bg-[#312c32] min-h-[533px] sm:min-w-[400px] min-w-[350px] rounded-2xl flex flex-col items-center"
    >
      <span class="text-2xl sm:text-3xl mt-5 opacity-100">Login Form</span>

      <form
        @submit.prevent="onSubmit"
        class="bg-[#413b43] w-80 sm:w-90 h-110 mt-3 rounded-xl flex flex-col"
      >
        <div
          class="flex flex-col justify-center items-center w-full gap-10 mt-10"
        >
          <FormField v-slot="{ componentField }" name="email">
            <FormItem class="w-8/9">
              <FormLabel>E-Mail</FormLabel>
              <FormControl>
                <Input
                  type="text"
                  placeholder="Write your email..."
                  v-bind="componentField"
                  class="rounded-[none] shadow-md"
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          </FormField>
          <FormField v-slot="{ componentField }" name="password">
            <FormItem class="w-8/9">
              <FormLabel>Password</FormLabel>
              <FormControl>
                <Input
                  type="text"
                  placeholder="Write your password..."
                  v-bind="componentField"
                  class="rounded-[none] shadow-md aria-invalid:border-destructive"
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          </FormField>
        </div>
        <div class="flex flex-col items-center gap-3 mt-7">
          <a
            class="cursor-pointer underline"
            @click="navigateTo('/registration')"
            >I want to register</a
          >
          <Button type="submit" class="button w-1/2 text-xl mt-10" size="lg">
            Submit
          </Button>
        </div>
      </form>
    </div>
  </div>
</template>
