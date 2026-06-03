# Laravel Backend Conventions

## Project Structure

```
app/
├── Console/Commands/          # Artisan commands (jika ada)
├── Enums/                     # Semua enum: MemberStatus, PaymentType, SurveyLevel, dll
├── Exceptions/                # Custom exception + Handler.php untuk format error
├── Http/
│   ├── Controllers/Api/V1/    # Semua controller API, namespace App\Http\Controllers\Api\V1
│   ├── Middleware/            # Custom middleware
│   ├── Requests/              # Form Request per endpoint
│   └── Resources/             # API Resource (transformasi response)
├── Jobs/                      # Queue jobs
├── Mail/                      # Mailable classes
├── Models/                    # Eloquent models
├── Policies/                  # Authorization policies (RBAC + wilayah scope)
└── Services/                  # Business logic layer (dipanggil dari controller)

routes/
└── api.php                    # Semua route API, dikelompokkan per versi dan domain
```

---

## Routing

Semua route API berada di `routes/api.php` dengan prefix `/api/v1`.

```php
// routes/api.php
Route::prefix('v1')->group(function () {

    // Public routes (no auth)
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/payment/webhook', [PaymentController::class, 'webhook']); // Midtrans
    Route::get('/verify/{certNumber}', [CertificateController::class, 'publicVerify']);

    // Wilayah (public, no auth needed)
    Route::prefix('wilayah')->group(function () {
        Route::get('/provinsi', [WilayahController::class, 'provinsi']);
        Route::get('/kota/{provinceCode}', [WilayahController::class, 'kota']);
        Route::get('/kecamatan/{cityCode}', [WilayahController::class, 'kecamatan']);
    });

    // Authenticated routes
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Member routes
        Route::prefix('member')->group(function () {
            Route::get('/', [MemberController::class, 'show']);
            Route::put('/', [MemberController::class, 'update']);
            Route::post('/complete-profile', [MemberController::class, 'completeProfile']);
        });

        // Payment
        Route::prefix('payment')->group(function () {
            Route::post('/create', [PaymentController::class, 'create']);
            Route::get('/{payment}/status', [PaymentController::class, 'status']);
        });

        // Certificate
        Route::get('/certificate/{certificate}/download', [CertificateController::class, 'download']);

        // Survey (petugas)
        Route::prefix('survey')->middleware('role:dpc,dpd,dpp,super_admin')->group(function () {
            Route::get('/', [SurveyController::class, 'index']);
            Route::get('/{assignment}', [SurveyController::class, 'show']);
            Route::post('/{assignment}/accept', [SurveyController::class, 'accept']);
            Route::post('/{assignment}/approve', [SurveyController::class, 'approve']);
            Route::post('/{assignment}/reject', [SurveyController::class, 'reject']);
        });

        // Admin routes
        Route::prefix('admin')->middleware('role:dpc,dpd,dpp,super_admin')->group(function () {
            Route::apiResource('members', AdminMemberController::class)->only(['index', 'show']);
            Route::apiResource('sanctions', SanctionController::class)->only(['index', 'store']);
            Route::apiResource('refunds', RefundController::class)->only(['index', 'update']);
            Route::get('/refunds/export', [RefundController::class, 'export']);
            Route::apiResource('starterkit-items', StarterkitItemController::class);
            Route::apiResource('starterkit-distributions', StarterkitDistributionController::class)
                ->only(['index', 'update']);
        });

        // Super Admin only
        Route::prefix('admin')->middleware('role:super_admin')->group(function () {
            Route::apiResource('users', UserManagementController::class);
            Route::apiResource('organizational-units', OrgUnitController::class);
            Route::get('/settings', [SettingController::class, 'index']);
            Route::put('/settings', [SettingController::class, 'update']);
        });
    });
});
```

---

## API Response Format

**Setiap response harus menggunakan format envelope yang sama.** Gunakan helper trait di semua controller.

```php
// app/Http/Controllers/Api/V1/BaseController.php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    protected function success(mixed $data, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function successPaginated($resource, string $message = 'OK'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $resource->items(),
            'meta'    => [
                'current_page' => $resource->currentPage(),
                'last_page'    => $resource->lastPage(),
                'per_page'     => $resource->perPage(),
                'total'        => $resource->total(),
            ],
        ]);
    }

    protected function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $body = ['success' => false, 'message' => $message];
        if (!empty($errors)) {
            $body['errors'] = $errors;
        }
        return response()->json($body, $status);
    }
}
```

**Response shape ringkasan:**

```json
// Success (single object)
{ "success": true, "message": "...", "data": { ... } }

// Success (list + pagination)
{ "success": true, "message": "...", "data": [ ... ], "meta": { "current_page": 1, "last_page": 5, "per_page": 15, "total": 72 } }

// Validation error (422)
{ "success": false, "message": "Data tidak valid.", "errors": { "email": ["Email sudah digunakan."] } }

// Auth error (401/403)
{ "success": false, "message": "Tidak terautentikasi." }

// Not found (404)
{ "success": false, "message": "Data tidak ditemukan." }
```

Daftarkan handler di `app/Exceptions/Handler.php` untuk memastikan semua exception (termasuk `ValidationException`, `AuthenticationException`, `ModelNotFoundException`) dikembalikan dalam format ini — bukan format default Laravel.

---

## Controllers

- Semua controller extends `BaseController`
- Controller hanya bertugas: validasi request, otorisasi, panggil Service, kembalikan response
- **Jangan taruh business logic di controller** — taruh di `app/Services/`
- Gunakan `$this->authorize()` di awal method yang butuh RBAC

```php
// Contoh controller yang benar
namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Survey\RejectSurveyRequest;
use App\Http\Resources\SurveyAssignmentResource;
use App\Models\SurveyAssignment;
use App\Services\SurveyService;

class SurveyController extends BaseController
{
    public function __construct(private SurveyService $surveyService) {}

    public function reject(RejectSurveyRequest $request, SurveyAssignment $assignment): JsonResponse
    {
        $this->authorize('reject', $assignment);  // Policy menangani scope wilayah

        $result = $this->surveyService->rejectAssignment($assignment, $request->validated());

        return $this->success(
            new SurveyAssignmentResource($result),
            'Pengajuan berhasil ditolak.'
        );
    }
}
```

---

## Form Requests

Buat satu `FormRequest` per endpoint yang menerima input. Letakkan di `app/Http/Requests/{Domain}/`.

```php
// app/Http/Requests/Survey/RejectSurveyRequest.php
namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;

class RejectSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi ditangani di controller via Policy
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.min'      => 'Alasan penolakan minimal 20 karakter.',
        ];
    }
}
```

---

## Models

```php
// Gunakan $fillable, bukan $guarded
// Selalu definisikan $casts untuk enum dan tipe data khusus
// Relationships ditulis dengan return type yang eksplisit

namespace App\Models;

use App\Enums\MemberStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    protected $fillable = [
        'user_id', 'membership_no', 'rental_name',
        'status', 'registered_at', 'expires_at', 'period_year',
    ];

    protected $casts = [
        'status'        => MemberStatus::class,
        'registered_at' => 'date',
        'expires_at'    => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function primaryBranch(): HasOne
    {
        return $this->hasOne(Branch::class)->where('is_primary', true);
    }

    public function surveyAssignments(): HasMany
    {
        return $this->hasMany(SurveyAssignment::class);
    }

    // Wilindo: relasi via code, bukan integer FK
    // Jangan definisikan sebagai Eloquent relationship
    // Gunakan helper di service/resource saat butuh data wilayah
}
```

---

## Enums

Semua status dan tipe harus didefinisikan sebagai PHP Enum di `app/Enums/`.

```php
// app/Enums/MemberStatus.php
namespace App\Enums;

enum MemberStatus: string
{
    case PendingVerification = 'pending_verification';
    case WaitingSurvey       = 'waiting_survey';
    case Active              = 'active';
    case Rejected            = 'rejected';
    case Expired             = 'expired';
}

// app/Enums/SurveyLevel.php
namespace App\Enums;

enum SurveyLevel: string
{
    case DPC = 'dpc';
    case DPD = 'dpd';
    case DPP = 'dpp';
}

// app/Enums/SurveyStatus.php
namespace App\Enums;

enum SurveyStatus: string
{
    case Pending   = 'pending';
    case Accepted  = 'accepted';
    case Approved  = 'approved';
    case Rejected  = 'rejected';
    case Escalated = 'escalated';
}

// app/Enums/PaymentType.php
namespace App\Enums;

enum PaymentType: string
{
    case Registration = 'registration';
    case Renewal      = 'renewal';
}

// app/Enums/PaymentStatus.php
namespace App\Enums;

enum PaymentStatus: string
{
    case Pending  = 'pending';
    case Paid     = 'paid';
    case Failed   = 'failed';
    case Expired  = 'expired';
    case Refunded = 'refunded';
}

// app/Enums/RefundStatus.php
namespace App\Enums;

enum RefundStatus: string
{
    case Queued     = 'queued';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
}

// app/Enums/SanctionSeverity.php
namespace App\Enums;

enum SanctionSeverity: string
{
    case Warning     = 'warning';
    case Suspension  = 'suspension';
    case Termination = 'termination';
}
```

---

## Policies (RBAC + Wilayah Scope)

Policy adalah satu-satunya tempat untuk cek otorisasi. Controller tidak boleh berisi logika wilayah.

```php
// app/Policies/SurveyAssignmentPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\SurveyAssignment;
use App\Enums\SurveyLevel;

class SurveyAssignmentPolicy
{
    public function view(User $user, SurveyAssignment $assignment): bool
    {
        return match ($user->role) {
            'super_admin', 'dpp' => true,
            'dpd' => $assignment->organizationalUnit->province_code
                        === $user->organizationalUnit?->province_code,
            'dpc' => $assignment->organizationalUnit->city_code
                        === $user->organizationalUnit?->city_code,
            default => false,
        };
    }

    public function accept(User $user, SurveyAssignment $assignment): bool
    {
        // Hanya petugas yang di-assign yang bisa accept
        return $assignment->assigned_to === $user->id
            && $assignment->status->value === 'pending';
    }

    public function reject(User $user, SurveyAssignment $assignment): bool
    {
        return $assignment->assigned_to === $user->id
            && $assignment->status->value === 'accepted';
    }
}
```

---

## Queue Jobs

```php
// app/Jobs/GenerateCertificateJob.php
namespace App\Jobs;

use App\Models\Member;
use App\Services\CertificateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateCertificateJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // detik antar retry

    public function __construct(private Member $member, private int $paymentId) {}

    public function handle(CertificateService $service): void
    {
        $service->generate($this->member, $this->paymentId);
    }

    public function failed(\Throwable $e): void
    {
        // Notif Super Admin via email
        \Mail::to(config('app.admin_email'))->send(new \App\Mail\JobFailedMail(
            job: 'GenerateCertificateJob',
            member: $this->member,
            error: $e->getMessage(),
        ));
    }
}
```

**Dispatch pattern:**
```php
// Selalu dispatch, jangan call langsung
GenerateCertificateJob::dispatch($member, $payment->id);

// Dengan delay
SendPaymentReminderJob::dispatch($member)->delay(now()->addMinutes(5));
```

---

## Migrations

```php
// Konvensi nama file: create_{table}_table, add_{column}_to_{table}_table
// Selalu gunakan ->comment() untuk kolom yang tidak self-explanatory
// Selalu definisikan index untuk kolom yang sering di-query

Schema::create('survey_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('member_id')->constrained()->cascadeOnDelete();
    $table->foreignId('assigned_to')->constrained('users');
    $table->foreignId('organizational_unit_id')->constrained();
    $table->string('level', 10)->comment('dpc|dpd|dpp');
    $table->string('status', 20)->default('pending');
    $table->text('rejection_reason')->nullable();
    $table->dateTime('deadline');
    $table->dateTime('accepted_at')->nullable();
    $table->dateTime('decided_at')->nullable();
    $table->unsignedBigInteger('escalated_from')->nullable()
        ->comment('self-referencing: FK ke survey_assignments.id');
    $table->foreign('escalated_from')->references('id')->on('survey_assignments');
    $table->timestamps();

    $table->index(['member_id', 'status']);
    $table->index(['assigned_to', 'status']);
    $table->index('deadline');
});
```

---

## Scheduled Commands (Shared Hosting)

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    $schedule->job(new \App\Jobs\CheckSurveyDeadlineJob)
        ->hourly()
        ->withoutOverlapping()
        ->onFailure(function () {
            \Log::error('CheckSurveyDeadlineJob gagal dijalankan.');
        });

    $schedule->job(new \App\Jobs\SendPaymentReminderJob)
        ->dailyAt('08:00')
        ->withoutOverlapping();
}
```

cPanel cron entry (dua baris terpisah):
```
* * * * * cd /home/user/public_html/daftar && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd /home/user/public_html/daftar && php artisan queue:work --stop-when-empty --tries=3 --timeout=60 >> /dev/null 2>&1
```
