<?php

namespace App\Http\Resources;

use DidiWijaya\WilIndo\Models\City;
use DidiWijaya\WilIndo\Models\District;
use DidiWijaya\WilIndo\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $branch = $this->primaryBranch;

        return [
            'id'            => $this->id,
            'membership_no' => $this->membership_no,
            'rental_name'   => $this->user->rental_name ?? null,
            'status'        => $this->status->value,
            'registered_at' => $this->registered_at?->toDateString(),
            'expires_at'    => $this->expires_at?->toDateString(),
            'period_year'   => $this->period_year,
            'user' => [
                'id'                => $this->user->id,
                'name'              => $this->user->name,
                'email'             => $this->user->email,
                'phone'             => $this->user->phone,
                'bank_name'         => $this->user->bank_name,
                'bank_account_no'   => $this->user->bank_account_no,
                'bank_account_name' => $this->user->bank_account_name,
            ],
            'primary_branch' => $branch ? [
                'id'            => $branch->id,
                'branch_name'   => $branch->branch_name,
                'province_code' => $branch->province_code,
                'province_name' => Province::where('code', $branch->province_code)->value('name'),
                'city_code'     => $branch->city_code,
                'city_name'     => City::where('code', $branch->city_code)->value('name'),
                'district_code' => $branch->district_code,
                'district_name' => $branch->district_code
                    ? District::where('code', $branch->district_code)->value('name')
                    : null,
                'address'       => $branch->address,
                'unit_count'    => $branch->unit_count,
                'document_url'  => $branch->document_path
                    ? asset('storage/' . $branch->document_path)
                    : null,
            ] : null,
            'latest_payment' => $this->whenLoaded('payments', function () {
                $payment = $this->payments->sortByDesc('created_at')->first();
                return $payment ? [
                    'id'              => $payment->id,
                    'status'          => $payment->status->value,
                    'type'            => $payment->type->value,
                    'amount'          => $payment->amount,
                    'paid_at'         => $payment->paid_at?->toIso8601String(),
                    'payment_channel' => $payment->payment_channel,
                ] : null;
            }),
            'latest_certificate' => $this->whenLoaded('certificates', function () {
                $cert = $this->certificates->sortByDesc('created_at')->first();
                return $cert ? [
                    'cert_number'  => $cert->cert_number,
                    'valid_until'  => $cert->valid_until?->toDateString(),
                    'download_url' => route('certificate.download', $cert->id),
                ] : null;
            }),
        ];
    }
}
