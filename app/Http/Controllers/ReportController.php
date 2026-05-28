<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateLargeReport;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        // Stock valuation: sum product values per warehouse using current product prices.
        $warehouseStats = Warehouse::where('is_active', true)
            ->withSum('stocks', 'quantity')
            ->get()
            ->map(fn($w) => [
                'name'        => $w->name,
                'city'        => $w->city,
                'total_stock' => $w->stocks_sum_quantity ?? 0,
                'total_value' => $w->stock_value,
            ]);

        $totalValue = $warehouseStats->sum('total_value');
        $totalStock = $warehouseStats->sum('total_stock');

        // Stock summary per product using aggregate quantity and current product prices.
        $stockSummary = WarehouseStock::join('products', 'warehouse_stocks.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.name as product_name',
                'products.sku',
                'categories.name as category',
                DB::raw('SUM(warehouse_stocks.quantity) AS total_qty'),
                DB::raw('SUM(warehouse_stocks.quantity * products.price) AS total_value'),
                'products.minimum_threshold',
                'products.unit',
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name', 'products.minimum_threshold', 'products.unit')
            ->orderByDesc('total_value')
            ->limit(20)
            ->get();

        // Monthly transaction stats (last 6 months)
        $monthlyStats = InventoryTransaction::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') AS month"),
                'type',
                DB::raw('SUM(quantity) AS total'),
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('reports.index', compact(
            'warehouseStats', 'totalValue', 'totalStock', 'stockSummary', 'monthlyStats', 'warehouses'
        ));
    }

    /**
     * Synchronous small report download (aliased as reports.generate in routes).
     * Routes by 'type' parameter: inventory (default) or transactions.
     */
    public function generate(Request $request): mixed
    {
        if ($request->input('type') === 'transactions') {
            return $this->generateTransactionPdfInline($request);
        }

        return $this->generateInventoryPdfInline($request);
    }

    /**
     * Async large report via queue (aliased as reports.generate-large in routes).
     */
    public function generateLarge(Request $request): RedirectResponse
    {
        $filename = 'laporan_inventaris_' . now()->format('Ymd_His') . '_' . Auth::id() . '.pdf';
        dispatch(new GenerateLargeReport($filename, Auth::id()));

        return redirect()->route('reports.index')
            ->with('success', "Laporan sedang dibuat di latar belakang. File: {$filename}. Refresh halaman dalam beberapa detik.")
            ->with('pending_report', $filename);
    }

    public function exportPdf(Request $request)
    {
        // For synchronous small export (immediate download)
        if ($request->get('mode') === 'sync') {
            return $this->generateInventoryPdfInline($request);
        }

        // Async: dispatch job and redirect with filename for polling
        $filename = 'laporan_inventaris_' . now()->format('Ymd_His') . '_' . Auth::id() . '.pdf';
        dispatch(new GenerateLargeReport($filename, Auth::id()));

        return redirect()->route('reports.index')
            ->with('success', "Laporan sedang dibuat di latar belakang. File: {$filename}. Refresh halaman dalam beberapa detik.")
            ->with('pending_report', $filename);
    }

    public function checkPdfStatus(string $filename)
    {
        // Sanitize filename — no path traversal
        $filename = basename($filename);
        $path     = "reports/{$filename}";

        if (!Storage::disk('local')->exists($path)) {
            if (request()->wantsJson()) {
                return response()->json(['ready' => false, 'message' => 'File tidak ditemukan atau sudah diunduh.'], 404);
            }
            return redirect()->route('reports.index')
                ->with('error', 'File laporan tidak ditemukan. Mungkin sudah diunduh sebelumnya atau belum selesai diproses.');
        }

        $content = Storage::disk('local')->get($path);

        // Schedule cleanup: hapus file lebih dari 24 jam
        $lastModified = Storage::disk('local')->lastModified($path);
        if (now()->timestamp - $lastModified > 86400) {
            Storage::disk('local')->delete($path);
        }

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length'      => strlen($content),
        ]);
    }

    /**
     * Laporan Inventaris — stok saat ini per gudang, dengan filter opsional.
     */
    private function generateInventoryPdfInline(Request $request): Response
    {
        $warehouseId = $request->input('warehouse_id');

        $warehouses = Warehouse::where('is_active', true)->get();

        $stockQuery = WarehouseStock::join('products', 'warehouse_stocks.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('warehouses', 'warehouse_stocks.warehouse_id', '=', 'warehouses.id')
            ->select(
                'products.name as product_name', 'products.sku', 'categories.name as category_name',
                'warehouses.name as warehouse_name', 'warehouses.city',
                'warehouse_stocks.quantity',
                DB::raw('warehouse_stocks.quantity * products.price AS nilai'),
                'products.minimum_threshold',
            )
            ->orderBy('warehouses.city')->orderBy('products.name');

        if ($warehouseId) {
            $stockQuery->where('warehouse_stocks.warehouse_id', $warehouseId);
        }

        $stockSummary  = $stockQuery->get();
        $totalValue    = $stockSummary->sum('nilai');
        $totalStock    = $stockSummary->sum('quantity');
        $criticalItems = $stockSummary->filter(fn($s) => $s->quantity <= $s->minimum_threshold);

        $recentTransactions = InventoryTransaction::with(['product', 'warehouse', 'operator'])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->orderByDesc('created_at')->take(30)->get();

        $logoBase64   = $this->buildLogoBase64(100);
        $filterLabel  = $warehouseId
            ? ($warehouses->firstWhere('id', $warehouseId)?->name ?? 'Gudang Terpilih')
            : 'Semua Gudang';

        $pdf = Pdf::loadView('reports.inventory-pdf', compact(
            'warehouses', 'stockSummary', 'totalValue', 'totalStock',
            'criticalItems', 'recentTransactions', 'logoBase64', 'filterLabel'
        ))
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'defaultFont'             => 'DejaVu Sans',
            'isHtml5ParserEnabled'    => true,
            'isRemoteEnabled'         => false,
            'isFontSubsettingEnabled' => true,
            'dpi' => 100,
        ]);

        $suffix   = $warehouseId ? '_gudang' . $warehouseId : '_semua';
        $filename = 'SmartStock_Laporan_Inventaris' . $suffix . '_' . now()->format('d-m-Y') . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Laporan Transaksi — histori masuk/keluar dalam rentang tanggal.
     */
    private function generateTransactionPdfInline(Request $request): Response
    {
        $dateFrom = $request->filled('date_from')
            ? \Carbon\Carbon::parse($request->input('date_from'))->startOfDay()
            : now()->startOfMonth();

        $dateTo = $request->filled('date_to')
            ? \Carbon\Carbon::parse($request->input('date_to'))->endOfDay()
            : now()->endOfDay();

        $transactions = InventoryTransaction::with(['product', 'warehouse', 'operator'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderByDesc('created_at')
            ->get();

        $totalIn  = $transactions->where('type', 'masuk')->sum('quantity');
        $totalOut = $transactions->where('type', 'keluar')->sum('quantity');

        // Rekap per gudang
        $warehouseSummary = InventoryTransaction::join('warehouses', 'inventory_transactions.warehouse_id', '=', 'warehouses.id')
            ->select(
                'warehouses.name as warehouse_name',
                DB::raw('COUNT(*) as jumlah_transaksi'),
                DB::raw("SUM(CASE WHEN type='masuk'  THEN quantity ELSE 0 END) AS total_masuk"),
                DB::raw("SUM(CASE WHEN type='keluar' THEN quantity ELSE 0 END) AS total_keluar"),
            )
            ->whereBetween('inventory_transactions.created_at', [$dateFrom, $dateTo])
            ->groupBy('warehouses.id', 'warehouses.name')
            ->orderBy('warehouses.name')
            ->get();

        $activeWarehouses = $warehouseSummary->count();
        $logoBase64       = $this->buildLogoBase64(100);

        $pdf = Pdf::loadView('reports.transaction-pdf', compact(
            'transactions', 'totalIn', 'totalOut',
            'warehouseSummary', 'activeWarehouses',
            'dateFrom', 'dateTo', 'logoBase64'
        ))
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'defaultFont'             => 'DejaVu Sans',
            'isHtml5ParserEnabled'    => true,
            'isRemoteEnabled'         => false,
            'isFontSubsettingEnabled' => true,
            'dpi' => 100,
        ]);

        $filename = 'SmartStock_Laporan_Transaksi_' . $dateFrom->format('d-m-Y') . '_sd_' . $dateTo->format('d-m-Y') . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Resize the logo PNG and return it as a data-URI-ready base64 string.
     * Falls back to the raw file if the GD extension is unavailable.
     */
    private function buildLogoBase64(int $targetHeight = 100): string
    {
        $path = public_path('smartstockpro.png');

        if (!file_exists($path)) {
            return '';
        }

        // Attempt resize via GD for a smaller PDF footprint
        if (extension_loaded('gd') && function_exists('imagecreatefrompng')) {
            try {
                $src = @imagecreatefrompng($path);
                if ($src) {
                    $origW = imagesx($src);
                    $origH = imagesy($src);
                    $scale = $targetHeight / $origH;
                    $newW  = (int) round($origW * $scale);
                    $newH  = $targetHeight;

                    $dst = imagecreatetruecolor($newW, $newH);
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

                    ob_start();
                    imagepng($dst, null, 6);
                    $data = ob_get_clean();

                    imagedestroy($src);
                    imagedestroy($dst);

                    return base64_encode($data);
                }
            } catch (\Throwable) {
                // fall through
            }
        }

        return base64_encode(file_get_contents($path));
    }
}
