import { ref, reactive } from 'vue'

export function usePagination(fetchFn) {
  const items = ref([])
  const meta = reactive({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
  const loading = ref(false)

  async function load(params = {}) {
    loading.value = true
    const { ok, data, meta: m } = await fetchFn({ page: meta.current_page, ...params })
    if (ok) {
      items.value = data
      Object.assign(meta, m)
    }
    loading.value = false
  }

  function goTo(page) {
    meta.current_page = page
    load()
  }

  return { items, meta, loading, load, goTo }
}
