import { ref, watch, nextTick } from 'vue'
import { useApi } from '@/composables/useApi'

/**
 * Cascade dropdown wilayah. WAJIB lazy-load: provinsi di-fetch saat dropdown
 * dibuka, kota saat provinsi dipilih, kecamatan saat kota dipilih. Data dari
 * API wilindo — jangan import data wilayah langsung ke frontend.
 */
export function useWilayah() {
  const api = useApi()

  const provinsiList = ref([])
  const kotaList = ref([])
  const kecamatanList = ref([])

  const selectedProvinsi = ref(null)
  const selectedKota = ref(null)
  const selectedKecamatan = ref(null)

  const loadingProvinsi = ref(false)
  const loadingKota = ref(false)
  const loadingKecamatan = ref(false)

  // Provinsi: lazy — dipanggil saat dropdown dibuka pertama kali
  async function fetchProvinsi() {
    if (provinsiList.value.length) return // sudah ada, skip
    loadingProvinsi.value = true
    const { ok, data } = await api.get('/wilayah/provinsi')
    if (ok) provinsiList.value = data
    loadingProvinsi.value = false
  }

  // Kota: fetch ulang setiap provinsi berubah
  watch(selectedProvinsi, async (code) => {
    kotaList.value = []
    kecamatanList.value = []
    selectedKota.value = null
    selectedKecamatan.value = null

    if (!code) return
    loadingKota.value = true
    const { ok, data } = await api.get(`/wilayah/kota/${code}`)
    if (ok) kotaList.value = data
    loadingKota.value = false
  })

  // Kecamatan: fetch ulang setiap kota berubah
  watch(selectedKota, async (code) => {
    kecamatanList.value = []
    selectedKecamatan.value = null

    if (!code) return
    loadingKecamatan.value = true
    const { ok, data } = await api.get(`/wilayah/kecamatan/${code}`)
    if (ok) kecamatanList.value = data
    loadingKecamatan.value = false
  })

  // Untuk edit form: set nilai awal dari data yang sudah ada
  async function prefill(provinceCode, cityCode, districtCode = null) {
    await fetchProvinsi()
    selectedProvinsi.value = provinceCode
    await nextTick()
    selectedKota.value = cityCode
    if (districtCode) {
      await nextTick()
      selectedKecamatan.value = districtCode
    }
  }

  return {
    provinsiList,
    kotaList,
    kecamatanList,
    selectedProvinsi,
    selectedKota,
    selectedKecamatan,
    loadingProvinsi,
    loadingKota,
    loadingKecamatan,
    fetchProvinsi,
    prefill,
  }
}
