<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'city',
        'lat',
        'lng',
        'address',
        'phone',
        'manager_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'lat'       => 'float',
            'lng'       => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->stocks()->sum('quantity');
    }

    public function getStockValueAttribute(): float
    {
        return $this->stocks()
            ->join('products', 'warehouse_stocks.product_id', '=', 'products.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('warehouse_stocks.quantity * products.price'));
    }
}
