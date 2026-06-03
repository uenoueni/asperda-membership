# Cross-Cutting Patterns

Pola-pola yang digunakan di lebih dari satu modul. Baca file ini saat mengerjakan fitur yang melibatkan payment, wilayah, PDF, atau eskalasi survey.

---

## Midtrans Integration

### Setup

```php
// config/midtrans.php
return [
    'server_key'    => env('MIDTRANS_SERVER_KEY'),
    'client_key'    => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
];

// AppServiceProvider.php boot()
\Midtrans\Config::$serverKey    = config('midtrans.server_key');
\Midtrans\Config::$isProduction = config('midtrans.is_production');
\Midtrans\Config::$isSanitized  = true;
\Midtrans\Config::$is3ds        = true;
```

### Membuat Snap Token

```php
// app/Services/PaymentService.php
public function createSnapToken(Member $member, string $type): array
{
    $payment = Payment::create([
        'member_id'        => $member->id,
        'midtrans_order_id'=> 'ASPERDA-' . $member->id . '-' . time(),
        'type'             => $type,
        'period_year'      => now()->year,
        'amount'           => $type === 'registration'
                                ? Setting::get('registration_fee')
                                : Setting::get('renewal_fee'),
        'status'           => PaymentStatus::Pending,
    ]);

    $params = [
        'transaction_details' => [
            'order_id'     => $payment->midtrans_order_id,
            'gross_amount' => (int) $payment->amount,
        ],
        'customer_details' => [
            'first_name' => $member->user->name,
            'email'      => $member->user->email,
            'phone'      => $member->user->phone,
        ],
    ];

    $snapToken = \Midtrans\Snap::getSnapToken($params);

    return ['payment' => $payment, 'snap_token' => $snapToken];
}
```

### Webhook Handler

```php
// app/Http/Controllers/Api/V1/PaymentController.php
public function webhook(Request $request): JsonResponse
{
    // 1. Verifikasi signature
    $notification = new \Midtrans\Notification();
    $expected     = hash('sha512',
        $notification->order_id .
        $notification->status_code .
        $notification->gross_amount .
        config('midtrans.server_key')
    );

    if ($notification->signature_key !== $expected) {
        return response()->json(['success' => false], 403);
    }

    // 2. Idempotency check
    $payment = Payment::where('midtrans_order_id', $notification->order_id)->first();
    if (!$payment) {
        return response()->json(['success' => true]); // unknown order, ignore
    }

    if ($payment->midtrans_transaction_id === $notification->transaction_id) {
        return response()->json(['success' => true]); // already processed
    }

    // 3. Update payment
    DB::transaction(function () use ($payment, $notification) {
        $payment->update([
            'midtrans_transaction_id' => $notification->transaction_id,
            'status'                  => $this->mapMidtransStatus($notification->transaction_status),
            'paid_at'                 => $notification->transaction_status === 'settlement' ? now() : null,
            'payment_channel'         => $notification->payment_type,
            'midtrans_raw_response'   => $notification->getResponse(),
        ]);

        if ($payment->status === PaymentStatus::Paid) {
            $this->paymentService->handlePaidPayment($payment);
        }
    });

    return response()->json(['success' => true]);
}

private function mapMidtransStatus(string $status): PaymentStatus
{
    return match ($status) {
        'settlement', 'capture' => PaymentStatus::Paid,
        'pending'               => PaymentStatus::Pending,
        'deny', 'cancel'        => PaymentStatus::Failed,
        'expire'                => PaymentStatus::Expired,
        default                 => PaymentStatus::Pending,
    };
}
```

### handlePaidPayment (dipanggil dalam transaction yang sama)

```php
// app/Services/PaymentService.php
public function handlePaidPayment(Payment $payment): void
{
    $member = $payment->member;

    if ($payment->type === PaymentType::Registration) {
        $member->update([
            'status'        => MemberStatus::WaitingSurvey,
            'registered_at' => now()->toDateString(),
        ]);
        $this->surveyService->createInitialAssignment($member);

    } elseif ($payment->type === PaymentType::Renewal) {
        $member->update([
            'status'      => MemberStatus::Active,
            'expires_at'  => $member->expires_at->addMonths(Setting::get('membership_duration_months')),
            'period_year' => now()->year,
        ]);
        GenerateCertificateJob::dispatch($member, $payment->id);
        $this->starterkitService->createDistributionsForMember($member, $payment->id);
    }
}
```

---

## Survey Escalation Pattern

### Membuat Assignment Awal (setelah payment registration)

```php
// app/Services/SurveyService.php
public function createInitialAssignment(Member $member): SurveyAssignment
{
    $cityCode = $member->primaryBranch->city_code;

    // Cari organizational unit DPC yang cover kota ini
    $unit = OrganizationalUnit::where('unit_type', 'dpc')
        ->where('city_code', $cityCode)
        ->where('is_active', true)
        ->firstOrFail();

    return SurveyAssignment::create([
        'member_id'              => $member->id,
        'assigned_to'            => $unit->user_id,
        'organizational_unit_id' => $unit->id,
        'level'                  => SurveyLevel::DPC,
        'status'                 => SurveyStatus::Pending,
        'deadline'               => now()->addDays(Setting::get('survey_deadline_dpc_days')),
        'escalated_from'         => null,
    ]);

    SendSurveyNotificationJob::dispatch($assignment);

    return $assignment;
}
```

### Eskalasi (dipanggil dari CheckSurveyDeadlineJob)

```php
// app/Services/SurveyService.php
public function escalate(SurveyAssignment $assignment): void
{
    $nextLevel = match ($assignment->level) {
        SurveyLevel::DPC => SurveyLevel::DPD,
        SurveyLevel::DPD => SurveyLevel::DPP,
        default => throw new \LogicException('DPP tidak bisa diescalate lebih lanjut.'),
    };

    DB::transaction(function () use ($assignment, $nextLevel) {
        // 1. Tandai assignment lama sebagai escalated
        $assignment->update(['status' => SurveyStatus::Escalated]);

        // 2. Buat sanksi untuk petugas yang lalai
        Sanction::create([
            'survey_assignment_id' => $assignment->id,
            'issued_to'            => $assignment->assigned_to,
            'issued_by'            => null, // system-generated
            'reason'               => "Tidak menyelesaikan tugas survey sebelum deadline ({$assignment->deadline}).",
            'severity'             => SanctionSeverity::Warning,
        ]);

        // 3. Cari unit untuk level berikutnya
        $unit = $this->findUnitForLevel($nextLevel, $assignment);

        // 4. Buat assignment baru
        $newAssignment = SurveyAssignment::create([
            'member_id'              => $assignment->member_id,
            'assigned_to'            => $unit->user_id,
            'organizational_unit_id' => $unit->id,
            'level'                  => $nextLevel,
            'status'                 => SurveyStatus::Pending,
            'deadline'               => now()->addDays(Setting::get("survey_deadline_{$nextLevel->value}_days")),
            'escalated_from'         => $assignment->id,
        ]);

        // 5. Notif level atas
        SendSurveyNotificationJob::dispatch($newAssignment);
    });
}

private function findUnitForLevel(SurveyLevel $level, SurveyAssignment $fromAssignment): OrganizationalUnit
{
    $query = OrganizationalUnit::where('unit_type', $level->value)->where('is_active', true);

    if ($level === SurveyLevel::DPD) {
        $provinceCode = $fromAssignment->organizationalUnit->province_code;
        $query->where('province_code', $provinceCode);
    }
    // DPP: tidak filter wilayah (nasional)

    return $query->firstOrFail();
}
```

### CheckSurveyDeadlineJob

```php
// app/Jobs/CheckSurveyDeadlineJob.php
public function handle(SurveyService $surveyService): void
{
    SurveyAssignment::query()
        ->where('status', SurveyStatus::Pending)
        ->where('deadline', '<', now())
        ->whereNull('accepted_at')
        ->where('level', '!=', SurveyLevel::DPP->value) // DPP tidak di-escalate
        ->each(function (SurveyAssignment $assignment) use ($surveyService) {
            try {
                $surveyService->escalate($assignment);
            } catch (\Throwable $e) {
                \Log::error("Gagal eskalasi assignment #{$assignment->id}: {$e->getMessage()}");
            }
        });
}
```

---

## Wilindo Integration

Wilindo menyediakan tabel `wilindo_provinces`, `wilindo_cities`, `wilindo_districts`, `wilindo_villages` dengan relasi via `code` varchar, bukan FK integer.

### Helper untuk resolve nama wilayah

```php
// app/Services/WilayahService.php
use DidiWijaya\WilIndo\Models\Province;
use DidiWijaya\WilIndo\Models\City;
use DidiWijaya\WilIndo\Models\District;

class WilayahService
{
    public function getProvinceName(string $code): string
    {
        return Province::where('code', $code)->value('name') ?? $code;
    }

    public function getCityName(string $code): string
    {
        return City::where('code', $code)->value('name') ?? $code;
    }

    public function getDistrictName(string $code): string
    {
        return District::where('code', $code)->value('name') ?? $code;
    }
}
```

### WilayahController (endpoint untuk frontend)

```php
// app/Http/Controllers/Api/V1/WilayahController.php
public function provinsi(): JsonResponse
{
    $data = \Cache::remember('wilayah.provinsi', 3600, fn() =>
        Province::orderBy('name')->get(['code', 'name'])
    );
    return $this->success($data);
}

public function kota(string $provinceCode): JsonResponse
{
    $data = \Cache::remember("wilayah.kota.{$provinceCode}", 3600, fn() =>
        City::where('province_code', $provinceCode)->orderBy('name')->get(['code', 'name'])
    );
    return $this->success($data);
}

public function kecamatan(string $cityCode): JsonResponse
{
    $data = \Cache::remember("wilayah.kecamatan.{$cityCode}", 3600, fn() =>
        District::where('city_code', $cityCode)->orderBy('name')->get(['code', 'name'])
    );
    return $this->success($data);
}
```

Cache 1 jam — data wilayah tidak berubah sering. Gunakan `php artisan cache:clear` jika wilindo di-reseed.

---

## PDF Sertifikat (dompdf)

### Service

```php
// app/Services/CertificateService.php
use Barryvdh\DomPDF\Facade\Pdf;

public function generate(Member $member, int $paymentId): Certificate
{
    $payment = Payment::findOrFail($paymentId);
    $branch  = $member->primaryBranch;

    // Generate nomor sertifikat
    $seq    = Certificate::where('period_year', $payment->period_year)->count() + 1;
    $certNo = sprintf('ASPERDA/%s/%s/%03d', $payment->period_year, $member->membership_no, $seq);

    // Render PDF dari Blade template
    $pdf = Pdf::loadView('certificates.template', [
        'member'      => $member,
        'branch'      => $branch,
        'certificate' => [
            'number'     => $certNo,
            'valid_from' => $member->registered_at->format('d F Y'),
            'valid_until'=> $member->expires_at->format('d F Y'),
        ],
        'approver'    => $this->getApprover($member),
        'qr_url'      => url('/verify/' . urlencode($certNo)),
    ])->setPaper('a4', 'landscape');

    $filename = 'certificates/' . str_replace('/', '-', $certNo) . '.pdf';
    \Storage::disk('local')->put($filename, $pdf->output());

    return Certificate::create([
        'member_id'    => $member->id,
        'payment_id'   => $paymentId,
        'cert_number'  => $certNo,
        'period_year'  => $payment->period_year,
        'valid_from'   => $member->registered_at,
        'valid_until'  => $member->expires_at,
        'file_path'    => $filename,
        'generated_at' => now(),
    ]);
}
```

### Template Blade (keterbatasan dompdf)

```blade
{{-- resources/views/certificates/template.blade.php --}}
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  /* PENTING: gunakan CSS 2.1 only — dompdf tidak support flexbox/grid */
  /* Gunakan table untuk layout kolom */
  /* Gunakan path absolut untuk gambar */
  body { font-family: DejaVu Sans, sans-serif; font-size: 12pt; }
  table { width: 100%; border-collapse: collapse; }
  .header-logo { width: 80px; }
  .qr-code { float: right; width: 80px; }
</style>
</head>
<body>
<table>
  <tr>
    {{-- Logo: gunakan base64 atau path absolut, BUKAN URL HTTP --}}
    <td><img src="{{ public_path('images/logo.png') }}" class="header-logo"></td>
    <td style="text-align:center;">
      <h2>SERTIFIKAT KEANGGOTAAN</h2>
      <p>ASOSIASI PENGUSAHA RENTAL KENDARAAN INDONESIA</p>
    </td>
  </tr>
</table>

<p>Nomor: {{ $certificate['number'] }}</p>
<p>Nama: {{ $member->user->name }}</p>
<p>Nama Usaha: {{ $member->rental_name }}</p>
<p>Berlaku: {{ $certificate['valid_from'] }} s.d. {{ $certificate['valid_until'] }}</p>

{{-- QR Code: generate sebagai base64 PNG --}}
<img src="data:image/png;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(100)->generate($qr_url)) }}" class="qr-code">

<p>{{ $approver['name'] }}<br>{{ $approver['role'] }}</p>
</body>
</html>
```

---

## App Settings Helper

```php
// app/Models/AppSetting.php
class AppSetting extends Model
{
    public $timestamps = false;

    // Penggunaan: Setting::get('registration_fee', 500000)
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::where('key', $key)->value('value');
        return $value ?? $default;
    }

    public static function set(string $key, mixed $value, int $updatedBy): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'updated_by' => $updatedBy]
        );
    }
}
```

**Key defaults yang harus di-seed:**

```php
// database/seeders/AppSettingSeeder.php
$settings = [
    ['key' => 'survey_deadline_dpc_days',       'value' => '3',       'description' => 'Tenggat DPC dalam hari'],
    ['key' => 'survey_deadline_dpd_days',       'value' => '3',       'description' => 'Tenggat DPD dalam hari'],
    ['key' => 'survey_deadline_dpp_days',       'value' => '5',       'description' => 'Tenggat DPP dalam hari'],
    ['key' => 'membership_duration_months',     'value' => '12',      'description' => 'Durasi keanggotaan aktif (bulan)'],
    ['key' => 'registration_fee',               'value' => '500000',  'description' => 'Biaya pendaftaran (Rp)'],
    ['key' => 'renewal_fee',                    'value' => '300000',  'description' => 'Biaya perpanjangan (Rp)'],
    ['key' => 'cert_number_prefix',             'value' => 'ASPERDA', 'description' => 'Prefix nomor sertifikat'],
    ['key' => 'membership_expiry_warning_days', 'value' => '30',      'description' => 'Hari sebelum exp untuk tampilkan notif'],
];
```

---

## Starterkit Distribution (Race Condition Safe)

```php
// app/Services/StarterkitService.php
public function createDistributionsForMember(Member $member, int $paymentId): void
{
    $items = StarterkitItem::where('period_year', $member->period_year)
        ->where('is_active', true)
        ->get();

    foreach ($items as $item) {
        DB::transaction(function () use ($item, $member, $paymentId) {
            // Lock row untuk hindari race condition
            $locked = StarterkitItem::lockForUpdate()->find($item->id);

            if ($locked->stock_distributed >= $locked->stock_total) {
                \Log::warning("Stok item #{$locked->id} habis, skip distribusi untuk member #{$member->id}");
                return;
            }

            StarterkitDistribution::create([
                'member_id'   => $member->id,
                'payment_id'  => $paymentId,
                'item_id'     => $item->id,
                'period_year' => $member->period_year,
                'status'      => 'pending',
            ]);

            $locked->increment('stock_distributed');
        });
    }
}
```
