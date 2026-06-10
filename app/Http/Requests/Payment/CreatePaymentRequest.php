<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $types = implode(',', array_column(\App\Enums\PaymentType::cases(), 'value'));
        return [
            'type' => ['required', 'string', "in:{$types}"],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Tipe pembayaran wajib diisi.',
            'type.in'       => 'Tipe pembayaran tidak valid.',
        ];
    }
}
