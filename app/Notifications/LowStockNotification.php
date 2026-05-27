<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Product $product,
        private readonly int $currentStock,
        private readonly int $warehouseId,
        private readonly string $severity,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $title = $this->severity === 'critical'
            ? "Stok Habis: {$this->product->name}"
            : "Stok Menipis: {$this->product->name}";

        $message = $this->severity === 'critical'
            ? "Produk «{$this->product->name}» telah kehabisan stok di Gudang #{$this->warehouseId}."
            : "Stok produk «{$this->product->name}» hanya tersisa {$this->currentStock} unit (min: {$this->product->minimum_threshold}).";

        return [
            'title'        => $title,
            'message'      => $message,
            'severity'     => $this->severity,
            'product_id'   => $this->product->id,
            'product_sku'  => $this->product->sku,
            'warehouse_id' => $this->warehouseId,
            'stock'        => $this->currentStock,
        ];
    }
}
