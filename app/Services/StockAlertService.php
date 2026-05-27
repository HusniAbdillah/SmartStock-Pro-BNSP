<?php

namespace App\Services;

use App\Models\ErrorLog;
use App\Models\Product;
use App\Models\WarehouseStock;
use App\Notifications\LowStockNotification;
use App\Models\User;

class StockAlertService
{
    public function checkAndAlert(int $productId, int $warehouseId): void
    {
        $stock = WarehouseStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$stock) {
            return;
        }

        $product = Product::find($productId);
        if (!$product) {
            return;
        }

        if ($stock->quantity > $product->minimum_threshold) {
            return;
        }

        $severity = $stock->quantity === 0 ? 'critical' : 'warning';

        $message = $severity === 'critical'
            ? "Stok produk «{$product->name}» (SKU: {$product->sku}) di gudang ID {$warehouseId} telah HABIS (0 unit)."
            : "Stok produk «{$product->name}» (SKU: {$product->sku}) di gudang ID {$warehouseId} mencapai batas minimum: {$stock->quantity} unit (min: {$product->minimum_threshold}).";

        // Check if similar unresolved alert already exists (avoid duplicates)
        $existing = ErrorLog::where('source', "stock_alert_{$productId}_{$warehouseId}")
            ->where('is_resolved', false)
            ->where('severity', $severity)
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        if (!$existing) {
            ErrorLog::create([
                'severity'   => $severity,
                'message'    => $message,
                'source'     => "stock_alert_{$productId}_{$warehouseId}",
                'context'    => [
                    'product_id'   => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity'     => $stock->quantity,
                    'threshold'    => $product->minimum_threshold,
                ],
                'created_at' => now(),
            ]);

            // Send in-app notification to all Admin and Manajer Gudang users
            User::whereIn('role', ['Admin', 'Manajer Gudang'])
                ->where('is_active', true)
                ->each(function (User $user) use ($product, $stock, $warehouseId, $severity) {
                    $user->notify(new LowStockNotification($product, $stock->quantity, $warehouseId, $severity));
                });
        }
    }
}
