<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'rental_name'       => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'             => ['required', 'string', 'max:20'],
            'bank_name'         => ['required', 'string', 'max:100'],
            'bank_account_no'   => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'              => 'Nama lengkap wajib diisi.',
            'rental_name.required'       => 'Nama usaha rental wajib diisi.',
            'email.required'             => 'Email wajib diisi.',
            'email.email'                => 'Format email tidak valid.',
            'email.unique'               => 'Email sudah terdaftar.',
            'phone.required'             => 'Nomor telepon wajib diisi.',
            'bank_name.required'         => 'Nama bank wajib diisi.',
            'bank_account_no.required'   => 'Nomor rekening wajib diisi.',
            'bank_account_name.required' => 'Nama pemilik rekening wajib diisi.',
        ];
    }
}
