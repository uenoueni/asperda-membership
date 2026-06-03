<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StarterkitItem extends Model
{
    protected $fillable = [
        'name',
        'description',
        'period_year',
        'stock_total',
        'stock_distributed',
        'is_active',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'stock_total'       => 'integer',
        'stock_distributed' => 'integer',
    ];

    public function distributions(): HasMany
    {
        return $this->hasMany(StarterkitDistribution::class, 'item_id');
    }

    public function stockAvailable(): int
    {
        return $this->stock_total - $this->stock_distributed;
    }
}
