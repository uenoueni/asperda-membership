import { ref } from 'vue'

const BASE_URL = import.meta.env.VITE_API_BASE_URL // mis: http://localhost:8000/api/v1

/**
 * WAJIB: semua HTTP request lewat composable ini. Jangan pakai fetch/axios langsung
 * di komponen atau file api/. Mengembalikan { ok, data, meta, message, error }.
 */
export function useApi() {
  const loading = ref(false)
  const error = ref(null)

  async function request(method, endpoint, body = null, options = {}) {
    loading.value = true
    error.value = null

    try {
      const config = {
        method,
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'include', // Sanctum cookie
        ...options,
      }

      if (body && method !== 'GET') {
        config.body = JSON.stringify(body)
      }

      const res = await fetch(`${BASE_URL}${endpoint}`, config)
      const json = await res.json()

      if (!res.ok) {
        error.value = json
        return { ok: false, data: null, error: json }
      }

      return { ok: true, data: json.data, meta: json.meta, message: json.message, error: null }
    } catch (e) {
      const networkError = { success: false, message: 'Koneksi gagal. Periksa jaringan Anda.' }
      error.value = networkError
      return { ok: false, data: null, error: networkError }
    } finally {
      loading.value = false
    }
  }

  const get = (endpoint, params = {}) => {
    const qs = new URLSearchParams(params).toString()
    return request('GET', qs ? `${endpoint}?${qs}` : endpoint)
  }
  const post = (endpoint, body) => request('POST', endpoint, body)
  const put = (endpoint, body) => request('PUT', endpoint, body)
  const patch = (endpoint, body) => request('PATCH', endpoint, body)
  const del = (endpoint) => request('DELETE', endpoint)

  return { loading, error, get, post, put, patch, del }
}
