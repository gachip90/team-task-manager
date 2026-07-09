<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout(): Promise<void> {
  await authStore.logout()
  await router.push({ name: 'login' })
}
</script>

<template>
  <v-app>
    <v-app-bar v-if="authStore.isAuthenticated" color="surface" border flat>
      <v-app-bar-title>Team Task Manager</v-app-bar-title>

      <v-spacer />

      <div v-if="authStore.user" class="user-summary">
        <span class="user-name">{{ authStore.user.name }}</span>
        <v-chip color="secondary" size="small" variant="tonal">
          {{ authStore.user.role }}
        </v-chip>
      </div>

      <v-btn
        icon="mdi-logout"
        variant="text"
        aria-label="Logout"
        title="Logout"
        @click="handleLogout"
      />
    </v-app-bar>

    <v-main>
      <RouterView />
    </v-main>
  </v-app>
</template>

<style>
html {
  overflow-y: auto;
}

body {
  margin: 0;
  background: #f7f8fb;
}

.user-summary {
  align-items: center;
  display: flex;
  gap: 10px;
  margin-right: 8px;
}

.user-name {
  color: #172033;
  font-size: 14px;
  font-weight: 600;
}
</style>
