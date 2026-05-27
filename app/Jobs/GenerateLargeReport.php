<?php

namespace App\Jobs;

use App\Models\ErrorLog;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GenerateLargeReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180;

    public function __construct(
        private readonly string $filename,
        private readonly int $userId,
        private readonly array $filters = [],
    ) {}

    public function handle(): void
    {
        // Gather comprehensive report data
        $warehouses = Warehouse::where('is_active', true)->get();

        $stockSummary = WarehouseStock::join('products', 'warehouse_stocks.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('warehouses', 'warehouse_stocks.warehouse_id', '=', 'warehouses.id')
            ->select(
                'products.name as product_name',
                'products.sku',
                'categories.name as category_name',
                'warehouses.name as warehouse_name',
                'warehouses.city',
                'warehouse_stocks.quantity',
                DB::raw('warehouse_stocks.quantity * products.price AS nilai'),
                'products.minimum_threshold',
            )
            ->orderBy('warehouses.city')
            ->orderBy('products.name')
            ->get();

        $totalValue   = $stockSummary->sum('nilai');
        $totalStock   = $stockSummary->sum('quantity');
        $criticalItems = $stockSummary->filter(fn($s) => $s->quantity <= $s->minimum_threshold);

        $recentTransactions = InventoryTransaction::with(['product', 'warehouse', 'operator'])
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        // Render PDF Blade template
        $pdf = Pdf::loadView('reports.inventory-pdf', compact(
            'warehouses',
            'stockSummary',
            'totalValue',
            'totalStock',
            'criticalItems',
            'recentTransactions',
        ))->setPaper('a4', 'landscape');

        // Save to storage
        $directory = 'reports';
        Storage::disk('local')->makeDirectory($directory);
        Storage::disk('local')->put("{$directory}/{$this->filename}", $pdf->output());

        ErrorLog::create([
            'severity'   => 'info',
            'message'    => "Laporan inventaris berhasil dibuat: {$this->filename}",
            'source'     => 'report_generation',
            'created_at' => now(),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        ErrorLog::create([
            'severity'    => 'critical',
            'message'     => "Pembuatan laporan gagal: {$e->getMessage()}",
            'stack_trace' => $e->getTraceAsString(),
            'source'      => 'report_generation',
            'created_at'  => now(),
        ]);
    }
}
