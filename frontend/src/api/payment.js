import { useApi } from '@/composables/useApi'

export function usePaymentApi() {
  const api = useApi()

  return {
    loading: api.loading,
    error:   api.error,

    create:    (body)  => api.post('/payment/create', body),
    getStatus: (id)    => api.get(`/payment/${id}/status`),
  }
}
