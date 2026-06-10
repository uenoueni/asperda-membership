<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WilindoDistrictSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $this->command->info('Mengambil data kecamatan dari laravolt/indonesia...');

            $response = Http::timeout(120)->get(
                'https://raw.githubusercontent.com/laravolt/indonesia/master/resources/csv/districts.csv'
            );

            if (!$response->successful()) {
                throw new \Exception('Gagal mengambil data. Status: ' . $response->status());
            }

            $districts = [];
            foreach (explode("\n", trim($response->body())) as $line) {
                $parts = str_getcsv($line);
                if (count($parts) < 3) continue;

                [$code, $cityCode, $name] = $parts;

                $districts[] = [
                    'code'       => trim($code),
                    'city_code'  => trim($cityCode),
                    'name'       => Str::title(Str::lower(trim($name))),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($districts)) {
                throw new \Exception('Tidak ada data kecamatan yang ditemukan');
            }

            DB::table(config('wilindo.prefix') . 'districts')->truncate();

            $totalInserted = 0;
            foreach (array_chunk($districts, 500) as $chunk) {
                DB::table(config('wilindo.prefix') . 'districts')->insert($chunk);
                $totalInserted += count($chunk);
                $this->command->info("Memproses {$totalInserted} dari " . count($districts) . " data kecamatan...");
            }

            $this->command->info('Berhasil menyimpan ' . count($districts) . ' data kecamatan');

        } catch (\Exception $e) {
            $this->command->error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
