<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilindoAreaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->call([
            WilindoProvinceSeeder::class,
            WilindoCitySeeder::class,
            WilindoDistrictSeeder::class,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
