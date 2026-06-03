<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'survey_deadline_dpc_days',    'value' => '3',       'description' => 'Tenggat DPC dalam hari'],
            ['key' => 'survey_deadline_dpd_days',    'value' => '3',       'description' => 'Tenggat DPD setelah eskalasi dalam hari'],
            ['key' => 'survey_deadline_dpp_days',    'value' => '5',       'description' => 'Tenggat DPP setelah eskalasi dalam hari'],
            ['key' => 'membership_duration_months',  'value' => '12',      'description' => 'Durasi keanggotaan aktif dalam bulan'],
            ['key' => 'registration_fee',            'value' => '500000',  'description' => 'Biaya pendaftaran dalam rupiah'],
            ['key' => 'renewal_fee',                 'value' => '300000',  'description' => 'Biaya perpanjangan dalam rupiah'],
            ['key' => 'cert_number_prefix',          'value' => 'ASPERDA', 'description' => 'Prefix nomor sertifikat'],
        ];

        foreach ($defaults as $setting) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['updated_at' => now()]),
            );
        }
    }
}
