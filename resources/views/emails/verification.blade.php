<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verifikasi Email — ASPERDA</title>
<style>
  body { margin: 0; padding: 0; background: #f4f6f9; font-family: 'Segoe UI', Arial, sans-serif; color: #1a2d44; }
  .wrap { max-width: 580px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
  .header { background: #1a3a5c; padding: 32px 40px; text-align: center; }
  .header h1 { margin: 0; color: #ffffff; font-size: 22px; letter-spacing: 0.1em; font-weight: 700; }
  .header p { margin: 6px 0 0; color: rgba(255,255,255,0.65); font-size: 13px; letter-spacing: 0.04em; }
  .body { padding: 40px; }
  .body p { margin: 0 0 16px; line-height: 1.65; font-size: 15px; color: #374151; }
  .btn-wrap { text-align: center; margin: 32px 0; }
  .btn { display: inline-block; background: #c03a2b; color: #ffffff !important; padding: 14px 36px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; letter-spacing: 0.02em; }
  .note { background: #f8f9fa; border-left: 4px solid #1a3a5c; padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 24px 0 0; }
  .note p { margin: 0; font-size: 13px; color: #6b7280; }
  .url-fallback { word-break: break-all; font-size: 12px; color: #9ca3af; margin-top: 8px; }
  .footer { background: #f4f6f9; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb; }
  .footer p { margin: 0; font-size: 12px; color: #9ca3af; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>ASPERDA</h1>
    <p>Asosiasi Pengusaha Rental Kendaraan Indonesia</p>
  </div>
  <div class="body">
    <p>Halo, <strong>{{ $user->name }}</strong>,</p>
    <p>Terima kasih telah mendaftar sebagai anggota ASPERDA. Klik tombol di bawah untuk memverifikasi email Anda dan melanjutkan ke langkah berikutnya.</p>
    <div class="btn-wrap">
      <a href="{{ $verificationUrl }}" class="btn">Verifikasi Email &amp; Buat Password</a>
    </div>
    <div class="note">
      <p>Link ini berlaku selama <strong>24 jam</strong>. Jika Anda tidak mendaftar di ASPERDA, abaikan email ini.</p>
      <p class="url-fallback">Tidak bisa klik tombol? Salin URL berikut ke browser:<br>{{ $verificationUrl }}</p>
    </div>
  </div>
  <div class="footer">
    <p>© {{ date('Y') }} ASPERDA Indonesia. Semua hak dilindungi.</p>
    <p>no-reply@asperda.id</p>
  </div>
</div>
</body>
</html>
