<script setup lang="ts">
import { Input } from "@/components/ui/input";
import { toTypedSchema } from "@vee-validate/zod";
import { useForm } from "vee-validate";
import * as z from "zod";

import {
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";

const formSchema = toTypedSchema(
  z.object({
    username: z.string().min(2).max(30),
    email: z.string().email(),
    password: z.string().min(6),
  })
);

const form = useForm({
  validationSchema: formSchema,
});

const onSubmit = form.handleSubmit((values) => {
  console.log("Form submitted!", values);
});
</script>

<template>
  <!-- <Input class="bg-[#4a444d]" placeholder="Username"></Input> -->
  <div class="flex flex-col justify-center items-center h-screen">
    <div
      class="bg-[#312c32] min-h-[533px] sm:min-w-[400px] min-w-[350px] rounded-2xl flex flex-col items-center"
    >
      <span class="text-2xl sm:text-3xl mt-5 opacity-100"
        >Registration Form</span
      >
      <form
        @submit="onSubmit"
        class="bg-[#413b43] w-90 h-110 mt-3 rounded-xl flex flex-col"
      >
        <FormField v-slot="{ componentField }" name="username">
          <FormItem class="mt-5 w-8/9 ml-5">
            <FormLabel>Username</FormLabel>
            <FormControl>
              <Input
                type="text"
                placeholder="shadcn"
                v-bind="componentField"
                class=""
              />
            </FormControl>
            <FormDescription>
              This is your public display name.
            </FormDescription>
            <FormMessage />
          </FormItem>
        </FormField>
        <FormField v-slot="{ componentField }" name="username">
          <FormItem class="mt-5 w-8/9 ml-5">
            <FormLabel>Username</FormLabel>
            <FormControl>
              <Input
                type="text"
                placeholder="shadcn"
                v-bind="componentField"
                class=""
              />
            </FormControl>
            <FormDescription>
              This is your public display name.
            </FormDescription>
            <FormMessage />
          </FormItem>
        </FormField>
        <FormField v-slot="{ componentField }" name="username">
          <FormItem class="mt-5 w-8/9 ml-5">
            <FormLabel>Username</FormLabel>
            <FormControl>
              <Input
                type="text"
                placeholder="shadcn"
                v-bind="componentField"
                class=""
              />
            </FormControl>
            <FormDescription>
              This is your public display name.
            </FormDescription>
            <FormMessage />
          </FormItem>
        </FormField>
        <Button type="submit"> Submit </Button>
      </form>
    </div>
  </div>
</template>
