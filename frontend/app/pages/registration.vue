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

import { useUserStore } from "~/store/userStore";
const userStore = useUserStore();
const formSchema = toTypedSchema(
  z.object({
    username: z.string().min(2).max(30),
    email: z.string().email(),
    password: z.string().min(8),
  })
);

const form = useForm({
  validationSchema: formSchema,
});

const onSubmit = form.handleSubmit(async (values) => {
  await userStore.fetchRegister(values);
});
</script>

<template>
  <div class="flex flex-col justify-center items-center h-screen">
    <div
      class="bg-[#312c32] min-h-[533px] sm:min-w-[400px] min-w-[350px] rounded-2xl flex flex-col items-center"
    >
      <span class="text-2xl sm:text-3xl mt-5 opacity-100"
        >Registration Form</span
      >
      <form
        @submit.prevent="onSubmit"
        class="bg-[#413b43] w-80 sm:w-90 h-110 mt-3 rounded-xl flex flex-col justify-evenly items-center"
      >
        <FormField v-slot="{ componentField }" name="username">
          <FormItem class="w-8/9">
            <FormLabel>Username</FormLabel>
            <FormControl>
              <Input
                type="text"
                placeholder="Write your username..."
                v-bind="componentField"
                class="rounded-[none] shadow-md"
              />
            </FormControl>
            <FormMessage />
          </FormItem>
        </FormField>
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
                type="password"
                placeholder="Write your password..."
                v-bind="componentField"
                class="rounded-[none] shadow-md aria-invalid:border-destructive"
              />
            </FormControl>
            <FormMessage />
          </FormItem>
          <a class="cursor-pointer underline" @click="navigateTo('/login')"
            >I want to login</a
          >
        </FormField>

        <Button type="submit" class="button w-1/2 text-xl" size="lg">
          Submit
        </Button>
      </form>
    </div>
  </div>
</template>
