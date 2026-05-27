<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateLargeReport;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Barryvdh\DomPDF\Facade\Pdf;
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
        // FIFO-style stock valuation: sum product values per warehouse
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

        // Stock summary with FIFO per product (ordered by created_at)
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
     */
    public function generate(Request $request): mixed
    {
        return $this->generatePdfInline();
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
            return $this->generatePdfInline();
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
        $path = "reports/{$filename}";

        if (Storage::disk('local')->exists($path)) {
            // Return the file
            $content = Storage::disk('local')->get($path);
            Storage::disk('local')->delete($path); // Clean up after download

            return response($content, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        return response()->json(['ready' => false]);
    }

    private function generatePdfInline(): Response
    {
        $warehouses = Warehouse::where('is_active', true)->get();

        $stockSummary = WarehouseStock::join('products', 'warehouse_stocks.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('warehouses', 'warehouse_stocks.warehouse_id', '=', 'warehouses.id')
            ->select(
                'products.name as product_name', 'products.sku', 'categories.name as category_name',
                'warehouses.name as warehouse_name', 'warehouses.city',
                'warehouse_stocks.quantity',
                DB::raw('warehouse_stocks.quantity * products.price AS nilai'),
                'products.minimum_threshold',
            )
            ->orderBy('warehouses.city')->orderBy('products.name')
            ->get();

        $totalValue    = $stockSummary->sum('nilai');
        $totalStock    = $stockSummary->sum('quantity');
        $criticalItems = $stockSummary->filter(fn($s) => $s->quantity <= $s->minimum_threshold);

        $recentTransactions = InventoryTransaction::with(['product', 'warehouse', 'operator'])
            ->orderByDesc('created_at')->take(30)->get();

        // Pre-encode logo as base64 in the controller so the view stays clean.
        // Resize to ~100px height to keep PDF file size reasonable.
        $logoBase64 = $this->buildLogoBase64(100);

        $pdf = Pdf::loadView('reports.inventory-pdf', compact(
            'warehouses', 'stockSummary', 'totalValue', 'totalStock',
            'criticalItems', 'recentTransactions', 'logoBase64'
        ))
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'defaultFont'  => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'     => false,
            'isFontSubsettingEnabled' => true,
            'dpi' => 100,
        ]);

        $filename = 'SmartStock_Pro_Laporan_Inventaris_' . now()->format('d-m-Y') . '.pdf';

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
