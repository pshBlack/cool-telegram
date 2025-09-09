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
import { useUserStore } from "~/stores/userStore";

const userStore = useUserStore();

const formSchema = toTypedSchema(
  z.object({
    email: z.string().email(),
    password: z.string().min(8),
  })
);

const form = useForm({
  validationSchema: formSchema,
});

const onSubmit = form.handleSubmit(async (values) => {
  await userStore.fetchLogin(values);
});

onMounted(async () => {
  if (useCookie("XSRF-TOKEN").value) await navigateTo("/chats");
  console.log(useCookie("XSRF-TOKEN").value);
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
