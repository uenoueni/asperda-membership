import { useApi } from '@/composables/useApi'

export function useAuthApi() {
  const api = useApi()

  return {
    loading: api.loading,
    error: api.error,

    register:             (payload)       => api.post('/auth/register', payload),
    login:                (payload)       => api.post('/auth/login', payload),
    logout:               ()              => api.post('/auth/logout'),
    me:                   ()              => api.get('/auth/me'),
    setPassword:          (payload)       => api.post('/auth/set-password', payload),
    resendVerification:   (email)         => api.post('/auth/resend-verification', { email }),
  }
}
