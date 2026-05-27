<?php

namespace App\Jobs;

use App\Services\StockAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckStockAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $productId,
        private readonly int $warehouseId,
    ) {}

    public function handle(StockAlertService $alertService): void
    {
        $alertService->checkAndAlert($this->productId, $this->warehouseId);
    }
}
