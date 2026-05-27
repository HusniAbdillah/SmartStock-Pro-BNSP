<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ErrorLog;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // KPI stats
        $totalProducts   = Product::active()->count();
        $totalWarehouses = Warehouse::where('is_active', true)->count();
        $totalStock      = WarehouseStock::sum('quantity');

        // Low stock products (total_stock <= minimum_threshold)
        $lowStockProducts = Product::active()
            ->with(['category', 'warehouseStocks'])
            ->get()
            ->filter(fn($p) => $p->total_stock <= $p->minimum_threshold)
            ->sortBy('total_stock')
            ->take(8)
            ->values();

        $lowStockCount = $lowStockProducts->count();

        // Transactions today (all types combined)
        $todayTransactions = InventoryTransaction::today()->count();

        // Last 7 days trend data for Chart.js
        $trendData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $trendData->push([
                'date'   => now()->subDays($i)->isoFormat('D MMM'),
                'masuk'  => InventoryTransaction::ofType('Masuk')->whereDate('created_at', $date)->sum('quantity'),
                'keluar' => InventoryTransaction::ofType('Keluar')->whereDate('created_at', $date)->sum('quantity'),
            ]);
        }

        // Stock per warehouse — used for the display table (Collection of stdClass)
        $warehouseStocks = Warehouse::where('is_active', true)
            ->withSum('stocks', 'quantity')
            ->get()
            ->map(function ($w) {
                return (object) [
                    'name'        => $w->name,
                    'city'        => $w->city ?? '',
                    'total_stock' => (int) ($w->stocks_sum_quantity ?? 0),
                ];
            });

        // Plain PHP array for JS chart (avoids ->map() inside @json() template)
        $warehouseStocksChart = $warehouseStocks
            ->map(fn($w) => ['name' => $w->name, 'total_stock' => $w->total_stock])
            ->values()
            ->all();

        // Map data: lat/lng + name + city for Leaflet
        $mapData = Warehouse::where('is_active', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get()
            ->map(fn($w) => [
                'name' => $w->name,
                'city' => $w->city ?? '',
                'lat'  => (float) $w->lat,
                'lng'  => (float) $w->lng,
            ])
            ->values()
            ->toArray();

        // Single flat array for @json($dashboardData) — must come after all its dependencies
        $dashboardData = [
            'trendLabels'     => $trendData->pluck('date')->values()->all(),
            'trendMasuk'      => $trendData->pluck('masuk')->values()->all(),
            'trendKeluar'     => $trendData->pluck('keluar')->values()->all(),
            'warehouseStocks' => $warehouseStocksChart,
            'mapData'         => $mapData,
        ];

        // Recent transactions
        $recentTransactions = InventoryTransaction::with(['product', 'warehouse', 'operator'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // Unresolved errors
        $unresolvedErrors = ErrorLog::unresolved()->count();

        return view('dashboard.index', compact(
            'totalProducts',
            'totalWarehouses',
            'totalStock',
            'lowStockCount',
            'lowStockProducts',
            'todayTransactions',
            'trendData',
            'warehouseStocks',
            'dashboardData',
            'mapData',
            'recentTransactions',
            'unresolvedErrors',
        ));
    }
}
