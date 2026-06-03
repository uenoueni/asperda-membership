<?php

namespace App\Http\Controllers\Api\V1;

use DidiWijaya\WilIndo\Models\City;
use DidiWijaya\WilIndo\Models\District;
use DidiWijaya\WilIndo\Models\Province;
use Illuminate\Http\JsonResponse;

class WilayahController extends BaseController
{
    public function provinsi(): JsonResponse
    {
        $data = Province::orderBy('name')->get(['code', 'name']);
        return $this->success($data);
    }

    public function kota(string $provinceCode): JsonResponse
    {
        $data = City::where('province_code', $provinceCode)
            ->orderBy('name')
            ->get(['code', 'name', 'province_code']);
        return $this->success($data);
    }

    public function kecamatan(string $cityCode): JsonResponse
    {
        $data = District::where('city_code', $cityCode)
            ->orderBy('name')
            ->get(['code', 'name', 'city_code']);
        return $this->success($data);
    }
}
