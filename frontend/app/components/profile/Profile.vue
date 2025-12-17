<template>
  <footer
    class="flex items-center justify-between p-3 bg-[#4a444c] mt-4 rounded-xl"
  >
    <div class="flex items-center">
      <div class="w-10 h-10 rounded-full bg-[#3a1016]"></div>
      <div class="flex flex-col ml-3">
        <span>{{ username ? username : useCookie("user") }}</span>

        <span class="text-xs text-(--muted-foreground)">Online</span>
      </div>
    </div>
    <Dialog>
      <DialogTrigger>
        <ProfileSettings />
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Edit Profile</DialogTitle>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="name" class="text-right"> Name </Label>
            <Input id="name" v-model="name" class="col-span-3" />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="email" class="text-right"> E-mail </Label>
            <Input id="email" v-model="email" class="col-span-3" />
          </div>
        </div>
        <DialogFooter>
          <Button type="submit"> Save changes </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </footer>
</template>
<script lang="ts" setup>
import { useUserStore } from "~/store/userStore";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";

const userStore = useUserStore();

const username = computed(() => userStore.user?.username);
const name = ref<any>(username ? useCookie("user") : "");
const email = ref<string>("");
</script>
