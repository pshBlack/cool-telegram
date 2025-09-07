<template>
  <div class="flex h-screen">
    <Sidebar />

    <main class="flex-1 flex items-center justify-center">
      <p class="text-gray-500">Виберіть чат</p>
    </main>
  </div>
</template>

<script lang="ts" setup>
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
    if (response.ok) {
      navigateTo("/chats");
    } else if (response.status === 401) {
      localStorage.removeItem("token");
      navigateTo("/login");
    }
  } catch (error) {
    console.error("Error:", error);
  }
};

onMounted(() => {
  const token = localStorage.getItem("token");
  if (token) {
    getUser(token);
  } else {
    navigateTo("/login");
  }
});
</script>
