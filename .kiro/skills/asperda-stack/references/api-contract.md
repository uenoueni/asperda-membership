# API Contract

Semua endpoint menggunakan base URL `/api/v1`.  
Format response selalu menggunakan envelope standar — lihat `references/laravel.md` bagian **API Response Format**.

---

## Auth

### POST /auth/register
**Request:**
```json
{
  "name": "Budi Santoso",
  "email": "budi@rental.com",
  "rental_name": "Rental Budi Jaya",
  "phone": "081234567890",
  "bank_name": "BCA",
  "bank_account_no": "1234567890",
  "bank_account_name": "Budi Santoso"
}
```
**Response 201:**
```json
{ "success": true, "message": "Registrasi berhasil. Cek email untuk verifikasi.", "data": null }
```

### POST /auth/verify-email
**Request:**
```json
{ "token": "<signed-url-token>", "password": "rahasia123", "password_confirmation": "rahasia123" }
```
**Response 200:**
```json
{ "success": true, "message": "Email terverifikasi. Silakan lengkapi profil.", "data": { "redirect": "/lengkapi-profil" } }
```

### POST /auth/login
**Request:** `{ "email": "...", "password": "..." }`  
**Response 200:** `{ "success": true, "message": "Login berhasil.", "data": { "user": { id, name, email, role } } }`

### POST /auth/logout
**Response 200:** `{ "success": true, "message": "Logout berhasil.", "data": null }`

### GET /auth/me
**Response 200:** `{ "success": true, "data": { "id": 1, "name": "...", "email": "...", "role": "member", "member": { ... } } }`

---

## Wilayah (Public, No Auth)

### GET /wilayah/provinsi
**Response 200:**
```json
{
  "success": true,
  "data": [
    { "code": "32", "name": "Jawa Barat" },
    { "code": "33", "name": "Jawa Tengah" }
  ]
}
```

### GET /wilayah/kota/{province_code}
**Response 200:**
```json
{
  "success": true,
  "data": [
    { "code": "3201", "name": "Kabupaten Bogor" },
    { "code": "3273", "name": "Kota Bandung" }
  ]
}
```

### GET /wilayah/kecamatan/{city_code}
**Response 200:** format sama, field `code` 7 digit.

---

## Member

### POST /member/complete-profile
Digunakan setelah verifikasi email untuk mengisi data keanggotaan.

**Request:**
```json
{
  "province_code": "32",
  "city_code": "3273",
  "district_code": "3273010",
  "address": "Jl. Sudirman No. 10, Bandung",
  "unit_count": 5,
  "branch_name": "Kantor Pusat Bandung"
}
```
**Response 201:**
```json
{ "success": true, "message": "Profil berhasil disimpan.", "data": { "member_id": 1, "status": "pending_verification" } }
```

### GET /member
**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "membership_no": "ASPERDA/001",
    "rental_name": "Rental Budi Jaya",
    "status": "active",
    "registered_at": "2025-01-15",
    "expires_at": "2026-01-15",
    "period_year": "2025",
    "primary_branch": {
      "branch_name": "Kantor Pusat Bandung",
      "city_code": "3273",
      "city_name": "Kota Bandung",
      "province_code": "32",
      "province_name": "Jawa Barat",
      "address": "...",
      "unit_count": 5
    },
    "latest_certificate": {
      "cert_number": "ASPERDA/2025/001/001",
      "valid_until": "2026-01-15",
      "download_url": "/api/v1/certificate/1/download"
    },
    "latest_payment": {
      "id": 1,
      "status": "paid",
      "amount": 500000,
      "paid_at": "2025-01-15T10:30:00Z"
    }
  }
}
```

### PUT /member
Partial update profil.  
**Request:** field yang ingin diupdate saja (name, phone, bank_name, bank_account_no, bank_account_name, address, unit_count).

---

## Payment

### POST /payment/create
**Request:**
```json
{ "type": "registration" }
// atau
{ "type": "renewal" }
```
**Response 201:**
```json
{
  "success": true,
  "data": {
    "payment_id": 10,
    "snap_token": "66e4fa55-fdac-4ef9-91b5-733b97d1b862",
    "order_id": "ASPERDA-1-1737000000",
    "amount": 500000
  }
}
```

### GET /payment/{id}/status
**Response 200:**
```json
{
  "success": true,
  "data": {
    "payment_id": 10,
    "status": "paid",
    "paid_at": "2025-01-15T10:30:00Z",
    "payment_channel": "bca_va"
  }
}
```

### POST /payment/webhook
Header `Content-Type: application/json`. Body adalah raw Midtrans notification object.  
**Response 200:** `{ "success": true }` — selalu 200 meski ada error internal (Midtrans retry jika non-200).

---

## Survey (Petugas)

### GET /survey
**Query params:** `status`, `level`, `page`, `per_page`  
**Response 200 (paginated):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "level": "dpc",
      "status": "pending",
      "deadline": "2025-01-18T10:30:00Z",
      "accepted_at": null,
      "member": {
        "id": 1,
        "rental_name": "Rental Budi Jaya",
        "primary_branch": { "city_name": "Kota Bandung", "unit_count": 5 }
      },
      "escalated_from": null
    }
  ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 40 }
}
```

### GET /survey/{id}
Detail assignment + data member lengkap + chain eskalasi.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "level": "dpd",
    "status": "pending",
    "deadline": "2025-01-21T10:30:00Z",
    "member": { /* data lengkap member */ },
    "escalation_chain": [
      { "id": 3, "level": "dpc", "status": "escalated", "assigned_to": { "name": "Agus" }, "deadline": "2025-01-18T10:30:00Z", "accepted_at": null }
    ]
  }
}
```

### POST /survey/{id}/accept
**Request:** `{}` (body kosong)  
**Response 200:** `{ "success": true, "message": "Tugas diterima.", "data": { "accepted_at": "..." } }`

### POST /survey/{id}/approve
**Request:** `{}` (body kosong)  
**Response 200:** `{ "success": true, "message": "Keanggotaan disetujui.", "data": { "member_status": "active" } }`

### POST /survey/{id}/reject
**Request:** `{ "rejection_reason": "Dokumen tidak lengkap..." }`  
**Response 200:** `{ "success": true, "message": "Pengajuan ditolak.", "data": { "refund_id": 2 } }`

---

## Refund (Admin)

### GET /admin/refunds
**Query params:** `status`, `period_year`, `page`  
**Response 200 (paginated):** list refund dengan data member dan rekening.

### PUT /admin/refunds/{id}
Untuk update status dan tanggal.  
**Request:**
```json
{
  "status": "processing",
  "planned_refund_date": "2025-01-20"
}
// atau saat completed:
{
  "status": "completed",
  "actual_refund_date": "2025-01-20",
  "notes": "Transfer via BCA"
}
```
**Response 200:** `{ "success": true, "message": "Refund diperbarui.", "data": { /* refund object */ } }`

### GET /admin/refunds/export
**Query params:** sama dengan GET /admin/refunds (filter ikut)  
**Response:** file CSV dengan header `Content-Disposition: attachment; filename="refund-export.csv"`

---

## Sertifikat

### GET /certificate/{id}/download
**Response:** PDF file, header:
```
Content-Type: application/pdf
Content-Disposition: attachment; filename="sertifikat-{cert_number}.pdf"
```

### GET /verify/{certNumber} (Public)
**Response 200:**
```json
{
  "success": true,
  "data": {
    "cert_number": "ASPERDA/2025/001/001",
    "member_name": "Budi Santoso",
    "rental_name": "Rental Budi Jaya",
    "valid_from": "2025-01-15",
    "valid_until": "2026-01-15",
    "is_valid": true
  }
}
```

---

## Error Codes Referensi

| HTTP Status | Kasus |
|---|---|
| 200 | Sukses (GET, PUT, PATCH) |
| 201 | Sukses create (POST) |
| 400 | Bad request (logika bisnis gagal, bukan validasi) |
| 401 | Tidak terautentikasi |
| 403 | Tidak punya akses (role/wilayah) |
| 404 | Resource tidak ditemukan |
| 422 | Validasi gagal (disertai `errors` object) |
| 500 | Server error |

---

## Pagination

Semua endpoint list yang menggunakan pagination mengembalikan format ini:

```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

Default `per_page`: 15. Max `per_page`: 100.  
Frontend menggunakan `usePagination` composable — lihat `references/vue.md`.
