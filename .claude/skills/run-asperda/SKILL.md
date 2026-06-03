---
name: run-asperda
description: >
  Run, start, build, launch, test, screenshot, or smoke-test the ASPERDA app.
  Use when asked to: start the dev server, run the app, verify a change works,
  check if the API is up, open the frontend, or confirm a feature works in the browser.
---

# run-asperda

ASPERDA terdiri dari dua proses terpisah yang harus jalan bersamaan:

- **Backend** — Laravel 11 REST API, dijalankan dengan `php artisan serve` dari root project.
- **Frontend** — Vue 3 + Vite PWA, dijalankan dengan `pnpm dev` dari `frontend/`.

**Driver (agent path):** smoke test PowerShell di `.claude/skills/run-asperda/smoke.ps1`.
Script ini mendeteksi proses yang sudah berjalan, lalu memverifikasi API ping dan frontend HTML.

---

## Prerequisites

Semua tersedia di environment ini:

- **PHP 8.3** — `php --version` → PHP 8.3.30
- **Node 22** — `node --version` → v22.22.0
- **pnpm 11** — `pnpm --version` → 11.5.0

Tidak perlu install tambahan untuk menjalankan dev server.

---

## Build

Frontend tidak perlu build untuk dev. Jika butuh production build:

```powershell
Set-Location frontend
pnpm build
# Output ke frontend/dist/
```

---

## Run (agent path) — GUNAKAN INI

Jalankan smoke test untuk memverifikasi kedua service berjalan:

```powershell
# Dari root project (asperda/)
powershell -File .claude/skills/run-asperda/smoke.ps1
```

Script menerima parameter opsional:

```powershell
# Jika port default sudah dipakai proses lain
powershell -File .claude/skills/run-asperda/smoke.ps1 -PortApi 8787 -PortFe 5173

# Jika kedua server sudah berjalan, skip cleanup
powershell -File .claude/skills/run-asperda/smoke.ps1 -PortApi 8787 -PortFe 5174 -NoCleanup
```

Smoke test memverifikasi:
1. `[PASS] PHP / Node / pnpm` tersedia
2. `[PASS] GET /api/v1/ping` → 200 `{"success":true,"data":{"app":"ASPERDA"}}`
3. `[PASS] GET http://localhost:{PortFe}/` → 200 HTML dengan `id="app"`
4. `[PASS] GET /api/v1/auth/me` (tanpa token) → non-200 (auth guard aktif)

Exit 0 = semua OK. Exit 1 = ada yang gagal.

### Menjalankan server secara manual (jika smoke test harus start server sendiri)

Script otomatis start kedua server jika belum berjalan. Tapi jika ingin manual:

**Backend (Laravel API):**
```powershell
# Dari root project (asperda/)
Set-Location "C:\Users\Salivs\Data\laragon\www\asperda"
php artisan serve --port=8787
# Server: http://127.0.0.1:8787
```

**Frontend (Vue + Vite):**
```powershell
# Dari folder frontend/
Set-Location "C:\Users\Salivs\Data\laragon\www\asperda\frontend"
pnpm dev
# Server: http://localhost:5173 (atau 5174 jika 5173 dipakai)
```

### Test endpoint API secara langsung

```powershell
# Ping — verifikasi API hidup
Invoke-WebRequest "http://127.0.0.1:8787/api/v1/ping" -UseBasicParsing | Select-Object -Exp Content

# Semua route terdaftar
php artisan route:list --path=api
```

---

## Run (human path)

Cara normal untuk development:
1. Jalankan Laragon (Apache + MySQL otomatis lewat tray icon)
2. Buka terminal ke `frontend/`, jalankan `pnpm dev`
3. Buka browser ke `http://localhost:5173`
4. Untuk API via Laragon: `http://asperda.test` (virtual host di `C:\Windows\System32\drivers\etc\hosts`)

**Catatan:** Laragon menggunakan Apache virtual host `asperda.test`. Untuk dev tanpa Laragon, gunakan `artisan serve` + update `frontend/.env`:
```
VITE_API_BASE_URL=http://127.0.0.1:8787/api/v1
```
(Default `.env` punya `http://localhost/asperda/public/api/v1` yang butuh Laragon.)

---

## Test

```powershell
# Unit test Laravel
Set-Location "C:\Users\Salivs\Data\laragon\www\asperda"
php artisan test
```

---

## Gotchas

**1. `curl` di PowerShell adalah alias `Invoke-WebRequest`, bukan curl.exe.**
Selalu gunakan `Invoke-WebRequest` atau `curl.exe` (binary curl terpisah) di skrip PowerShell. `curl -s URL` akan gagal dengan error "missing mandatory parameter: Uri".

**2. Vite auto-bump port.**
Jika port 5173 sudah dipakai (misal dari sesi dev sebelumnya yang tidak ditutup), Vite otomatis pakai port berikutnya (5174, 5175, dst). Smoke script mendeteksi port aktual dari output Vite. Selalu cek port di output terminal sebelum diasumsikan 5173.

**3. `/api/v1/auth/me` mengembalikan 500, bukan 401.**
Saat ini (Tahap 1, belum ada auth module), Laravel's `Authenticate` middleware mencoba redirect ke route bernama `login` untuk unauthenticated request, tapi route tersebut belum ada. Hasilnya 500 `RouteNotFoundException: Route [login] not defined`. Ini akan diperbaiki saat auth module (Tahap 1.8) selesai — dengan mendaftarkan route login atau mengoverride `redirectTo()` di `app/Http/Middleware/Authenticate.php` untuk mengembalikan 401 JSON bagi API request.

**4. `frontend/.env` punya dua konfigurasi API URL.**
- Untuk Laragon: `http://localhost/asperda/public/api/v1` (default saat ini)
- Untuk `artisan serve`: `http://127.0.0.1:8787/api/v1`
Jika frontend tidak bisa hit API, cek nilai `VITE_API_BASE_URL` di `frontend/.env` sesuai dengan cara backend dijalankan.

**5. `pwsh` tidak tersedia — gunakan `powershell`.**
Mesin ini hanya punya Windows PowerShell 5.1 (`powershell.exe`), bukan PowerShell Core (`pwsh`). Semua perintah PowerShell harus pakai `powershell`, bukan `pwsh`.

---

## Troubleshooting

| Gejala | Penyebab | Fix |
|--------|----------|-----|
| `Unable to connect` ke port 8787 | artisan serve belum jalan | Jalankan `php artisan serve --port=8787` |
| Vite di port 5173 tapi smoke test gagal | Vite auto-bump ke port lain | Jalankan `pnpm dev` dan lihat port di output, berikan `-PortFe` yang benar |
| `APP_KEY` tidak ada saat artisan serve | `.env` belum di-setup | Jalankan `php artisan key:generate` |
| `SQLSTATE[HY000]` saat artisan serve | MySQL tidak berjalan | Start Laragon atau MySQL service |
| Frontend HTML ada tapi Vue app kosong | `VITE_API_BASE_URL` salah atau CORS | Cek `frontend/.env` dan pastikan backend berjalan di port yang sesuai |
