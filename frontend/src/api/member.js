import { useApi } from '@/composables/useApi'

export function useMemberApi() {
  const api = useApi()

  return {
    loading: api.loading,
    error:   api.error,

    show:   ()      => api.get('/member'),
    update: (body)  => api.put('/member', body),

    completeProfile: (formData) => api.postForm('/member/complete-profile', formData),
  }
}
