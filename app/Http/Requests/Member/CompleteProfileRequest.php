<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class CompleteProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_name'   => ['required', 'string', 'max:150'],
            'province_code' => ['required', 'string'],
            'city_code'     => ['required', 'string'],
            'district_code' => ['nullable', 'string'],
            'address'       => ['required', 'string', 'min:10'],
            'unit_count'    => ['required', 'integer', 'min:1'],
            'document'          => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'bank_name'         => ['required', 'string', 'max:100'],
            'bank_account_no'   => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_name.required'   => 'Nama cabang wajib diisi.',
            'province_code.required' => 'Provinsi wajib dipilih.',
            'city_code.required'     => 'Kota/Kabupaten wajib dipilih.',
            'address.required'       => 'Alamat wajib diisi.',
            'address.min'            => 'Alamat minimal 10 karakter.',
            'unit_count.required'    => 'Jumlah unit wajib diisi.',
            'unit_count.min'         => 'Jumlah unit minimal 1.',
            'document.mimes'         => 'Dokumen harus berformat PDF, JPG, atau PNG.',
            'document.max'           => 'Ukuran dokumen maksimal 2 MB.',
            'bank_name.required'         => 'Nama bank wajib diisi.',
            'bank_account_no.required'   => 'Nomor rekening wajib diisi.',
            'bank_account_name.required' => 'Nama pemilik rekening wajib diisi.',
        ];
    }
}
