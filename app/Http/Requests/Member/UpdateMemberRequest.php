<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['sometimes', 'string', 'max:150'],
            'phone'             => ['sometimes', 'string', 'regex:/^(\+62|08)\d{7,13}$/'],
            'bank_name'         => ['sometimes', 'string', 'max:50'],
            'bank_account_no'   => ['sometimes', 'string', 'max:30'],
            'bank_account_name' => ['sometimes', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Format nomor telepon tidak valid (gunakan 08… atau +62…).',
        ];
    }
}
