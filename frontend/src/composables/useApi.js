import { ref } from 'vue'

const BASE_URL = import.meta.env.VITE_API_BASE_URL // mis: http://localhost:8000/api/v1
const APP_URL = BASE_URL.replace(/\/api\/v1\/?$/, '')  // mis: http://localhost:8000

function getCookie(name) {
  const match = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'))
  return match ? decodeURIComponent(match[1]) : null
}

// Panggil sekali di main.js sebelum app.mount() agar XSRF-TOKEN cookie tersedia.
export async function initCsrf() {
  await fetch(`${APP_URL}/sanctum/csrf-cookie`, { credentials: 'include' })
}

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
      const headers = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      }

      if (method !== 'GET') {
        const xsrf = getCookie('XSRF-TOKEN')
        if (xsrf) headers['X-XSRF-TOKEN'] = xsrf
      }

      const config = {
        method,
        headers,
        credentials: 'include', // Sanctum cookie
        ...options,
      }

      if (body && method !== 'GET') {
        config.body = JSON.stringify(body)
      }

      const res = await fetch(`${BASE_URL}${endpoint}`, config)
      const json = await res.json()

      if (!res.ok) {
        const err = { ...json, status: res.status }
        error.value = err
        return { ok: false, data: null, error: err }
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

  // Untuk upload file — kirim sebagai FormData (multipart/form-data)
  async function postForm(endpoint, formData) {
    loading.value = true
    error.value   = null
    try {
      const formHeaders = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        // Tidak set Content-Type — browser otomatis set boundary untuk multipart
      }
      const xsrf = getCookie('XSRF-TOKEN')
      if (xsrf) formHeaders['X-XSRF-TOKEN'] = xsrf

      const res = await fetch(`${BASE_URL}${endpoint}`, {
        method: 'POST',
        headers: formHeaders,
        credentials: 'include',
        body: formData,
      })
      const json = await res.json()
      if (!res.ok) {
        const err = { ...json, status: res.status }
        error.value = err
        return { ok: false, data: null, error: err }
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

  return { loading, error, get, post, put, patch, del, postForm }
}
