<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WilindoCitySeeder extends Seeder
{
    public function run(): void
    {
        try {
            $this->command->info('Mengambil data kabupaten/kota dari laravolt/indonesia...');

            $response = Http::timeout(60)->get(
                'https://raw.githubusercontent.com/laravolt/indonesia/master/resources/csv/cities.csv'
            );

            if (!$response->successful()) {
                throw new \Exception('Gagal mengambil data. Status: ' . $response->status());
            }

            $cities = [];
            foreach (explode("\n", trim($response->body())) as $line) {
                $parts = str_getcsv($line);
                if (count($parts) < 3) continue;

                [$code, $provinceCode, $name] = $parts;

                $cities[] = [
                    'code'          => trim($code),
                    'province_code' => trim($provinceCode),
                    'name'          => Str::title(Str::lower(trim($name))),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            if (empty($cities)) {
                throw new \Exception('Tidak ada data kabupaten/kota yang ditemukan');
            }

            DB::table(config('wilindo.prefix') . 'cities')->truncate();

            $totalInserted = 0;
            foreach (array_chunk($cities, 100) as $chunk) {
                DB::table(config('wilindo.prefix') . 'cities')->insert($chunk);
                $totalInserted += count($chunk);
                $this->command->info("Memproses {$totalInserted} dari " . count($cities) . " data kabupaten/kota...");
            }

            $this->command->info('Berhasil menyimpan ' . count($cities) . ' data kabupaten/kota');

        } catch (\Exception $e) {
            $this->command->error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
