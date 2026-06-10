<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengingat Pembayaran ASPERDA</title>
<style>
  body { margin: 0; padding: 0; background: #f4f6f9; font-family: Arial, sans-serif; font-size: 15px; color: #374151; }
  .wrapper { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
  .header { background: #1a3a5c; padding: 28px 32px; text-align: center; }
  .header h1 { margin: 0; color: #fff; font-size: 1.3rem; letter-spacing: 0.1em; }
  .header p  { margin: 6px 0 0; color: rgba(255,255,255,0.7); font-size: 0.85rem; }
  .body { padding: 28px 32px; }
  .greeting { font-size: 1rem; margin: 0 0 16px; }
  .info-box { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px 20px; margin: 20px 0; }
  .info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.9rem; }
  .info-row .label { color: #6b7280; }
  .info-row .value { font-weight: 600; color: #111827; }
  .amount { font-size: 1.35rem; font-weight: 800; color: #1a3a5c; }
  .cta { text-align: center; margin: 28px 0; }
  .btn { display: inline-block; background: #1a3a5c; color: #fff; text-decoration: none; padding: 14px 32px; border-radius: 10px; font-size: 1rem; font-weight: 700; }
  .note { font-size: 0.82rem; color: #9ca3af; line-height: 1.6; margin: 16px 0 0; }
  .footer { background: #f8fafc; padding: 16px 32px; text-align: center; font-size: 0.78rem; color: #9ca3af; border-top: 1px solid #e5e7eb; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>ASPERDA</h1>
    <p>Asosiasi Pengusaha Rental Kendaraan Indonesia</p>
  </div>
  <div class="body">
    <p class="greeting">Halo, <strong>{{ $memberName }}</strong>,</p>

    <p>
      Kami mengingatkan bahwa pembayaran pendaftaran keanggotaan ASPERDA atas nama
      <strong>{{ $rentalName }}</strong> belum diselesaikan.
    </p>

    <div class="info-box">
      <div class="info-row">
        <span class="label">Nomor Order</span>
        <span class="value">{{ $orderId }}</span>
      </div>
      <div class="info-row">
        <span class="label">Tanggal Daftar</span>
        <span class="value">{{ $createdAt }}</span>
      </div>
      <div class="info-row">
        <span class="label">Nominal</span>
        <span class="value amount">Rp {{ $amount }}</span>
      </div>
    </div>

    <p>
      Segera selesaikan pembayaran agar proses verifikasi keanggotaan Anda dapat dimulai.
    </p>

    <div class="cta">
      <a href="{{ $paymentUrl }}" class="btn">Bayar Sekarang</a>
    </div>

    <p class="note">
      Email ini dikirim secara otomatis. Jika Anda sudah menyelesaikan pembayaran, abaikan email ini.
      Butuh bantuan? Hubungi kami di <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.
    </p>
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} ASPERDA. Semua hak dilindungi.
  </div>
</div>
</body>
</html>
