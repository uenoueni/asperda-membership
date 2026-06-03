import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useApi } from '@/composables/useApi'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const api = useApi()

  const isAuthenticated = computed(() => !!user.value)
  const role = computed(() => user.value?.role ?? null)

  async function fetchUser() {
    const { ok, data } = await api.get('/auth/me')
    if (ok) user.value = data
    return ok
  }

  async function login(email, password) {
    const { ok, data, error } = await api.post('/auth/login', { email, password })
    if (ok) user.value = data
    return { ok, error }
  }

  async function logout() {
    await api.post('/auth/logout')
    user.value = null
  }

  return { user, isAuthenticated, role, fetchUser, login, logout }
})
