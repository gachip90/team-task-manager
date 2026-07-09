import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { api, TOKEN_STORAGE_KEY } from '@/services/api'
import type { LoginPayload, LoginResponse, User } from '@/types/api'

const USER_STORAGE_KEY = 'team_task_manager_user'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem(TOKEN_STORAGE_KEY))
  const user = ref<User | null>(readStoredUser())
  const isAuthenticated = computed(() => Boolean(token.value))

  async function login(payload: LoginPayload): Promise<void> {
    const { data } = await api.post<LoginResponse>('/login', payload)

    token.value = data.token
    user.value = data.user
    localStorage.setItem(TOKEN_STORAGE_KEY, data.token)
    localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(data.user))
  }

  async function logout(): Promise<void> {
    if (token.value) {
      await api.post('/logout').catch(() => undefined)
    }

    clearSession()
  }

  function clearSession(): void {
    token.value = null
    user.value = null
    localStorage.removeItem(TOKEN_STORAGE_KEY)
    localStorage.removeItem(USER_STORAGE_KEY)
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    logout,
    clearSession,
  }
})

function readStoredUser(): User | null {
  const rawUser = localStorage.getItem(USER_STORAGE_KEY)

  if (!rawUser) {
    return null
  }

  try {
    return JSON.parse(rawUser) as User
  } catch {
    localStorage.removeItem(USER_STORAGE_KEY)
    return null
  }
}
