<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'supplier_id',
        'type',
        'quantity',
        'source_warehouse_id',
        'destination_warehouse_id',
        'operator_id',
        'notes',
        'status',
        'reference_number',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_number)) {
                $model->reference_number = 'TRX-' . strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    // Scopes
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    // Helpers
    public function getTypeBadgeColorAttribute(): string
    {
        return match ($this->type) {
            'Masuk'    => 'bg-emerald-100 text-emerald-800',
            'Keluar'   => 'bg-red-100 text-red-800',
            'Transfer' => 'bg-amber-100 text-amber-800',
            default    => 'bg-slate-100 text-slate-600',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'bg-emerald-100 text-emerald-800',
            'pending'   => 'bg-amber-100 text-amber-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default     => 'bg-slate-100 text-slate-600',
        };
    }
}
