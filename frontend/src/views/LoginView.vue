<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { AxiosError } from 'axios'
import { useAuthStore } from '@/stores/auth'
import type { LoginPayload } from '@/types/api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const form = ref<LoginPayload>({
  email: '',
  password: '',
})
const isLoading = ref(false)
const errorMessage = ref('')

const canSubmit = computed(() => form.value.email.trim() !== '' && form.value.password !== '')

async function submitLogin(): Promise<void> {
  if (!canSubmit.value) {
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    await authStore.login(form.value)
    await router.push(typeof route.query.redirect === 'string' ? route.query.redirect : '/tasks')
  } catch (error) {
    errorMessage.value = getErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

function getErrorMessage(error: unknown): string {
  if (error instanceof AxiosError && error.response?.status === 422) {
    return 'Email or password is incorrect.'
  }

  return 'Unable to sign in right now.'
}
</script>

<template>
  <main class="login-page">
    <v-sheet class="login-panel" border rounded="lg">
      <div class="login-heading">
        <h1>Team Task Manager</h1>
        <p>Sign in to manage team tasks.</p>
      </div>

      <v-alert v-if="errorMessage" class="mb-4" density="compact" type="error" variant="tonal">
        {{ errorMessage }}
      </v-alert>

      <v-form @submit.prevent="submitLogin">
        <v-text-field
          v-model="form.email"
          autocomplete="email"
          label="Email"
          prepend-inner-icon="mdi-email-outline"
          type="email"
          variant="outlined"
        />

        <v-text-field
          v-model="form.password"
          autocomplete="current-password"
          label="Password"
          prepend-inner-icon="mdi-lock-outline"
          type="password"
          variant="outlined"
        />

        <v-btn
          block
          color="primary"
          :disabled="!canSubmit"
          :loading="isLoading"
          prepend-icon="mdi-login"
          size="large"
          type="submit"
        >
          Sign in
        </v-btn>
      </v-form>
    </v-sheet>
  </main>
</template>

<style scoped>
.login-page {
  align-items: center;
  background: #f7f8fb;
  display: flex;
  min-height: 100vh;
  padding: 24px;
}

.login-panel {
  margin: 0 auto;
  max-width: 420px;
  padding: 28px;
  width: 100%;
}

.login-heading {
  margin-bottom: 24px;
}

.login-heading h1 {
  color: #172033;
  font-size: 28px;
  font-weight: 700;
  line-height: 1.2;
  margin: 0 0 8px;
}

.login-heading p {
  color: #667085;
  font-size: 14px;
  margin: 0;
}
</style>
