<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\StockAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(private readonly StockAlertService $alertService) {}

    public function index(Request $request): View
    {
        $query = InventoryTransaction::with(['product', 'warehouse', 'operator', 'supplier'])
            ->orderByDesc('created_at');

        if ($type = $request->get('type')) {
            $query->ofType($type);
        }

        if ($productId = $request->get('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($warehouseId = $request->get('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $warehouses   = Warehouse::where('is_active', true)->get();

        return view('transactions.index', compact('transactions', 'warehouses'));
    }

    public function create(Request $request): View
    {
        $this->authorizeModify();

        $products   = Product::active()->with('category')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get();
        $selectedType = $request->get('type', 'Masuk');
        $selectedProduct = $request->get('product_id');

        return view('transactions.create', compact('products', 'warehouses', 'suppliers', 'selectedType', 'selectedProduct'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeModify();

        $validated = $request->validate([
            'product_id'   => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'type'         => ['required', 'in:Masuk,Keluar'],
            'quantity'     => ['required', 'integer', 'min:1'],
            'supplier_id'  => ['nullable', 'exists:suppliers,id'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ], [
            'product_id.required'   => 'Produk wajib dipilih.',
            'warehouse_id.required' => 'Gudang wajib dipilih.',
            'type.required'         => 'Tipe transaksi wajib dipilih.',
            'quantity.required'     => 'Jumlah wajib diisi.',
            'quantity.min'          => 'Jumlah minimal 1.',
        ]);

        $product      = Product::findOrFail($validated['product_id']);
        $warehouseId  = $validated['warehouse_id'];
        $qty          = $validated['quantity'];
        $type         = $validated['type'];

        // Validate sufficient stock for Keluar
        if ($type === 'Keluar') {
            $currentStock = $product->stockAtWarehouse($warehouseId);
            if ($currentStock < $qty) {
                return back()
                    ->withInput()
                    ->withErrors(['quantity' => "Stok tidak mencukupi. Stok tersedia di gudang ini: {$currentStock} {$product->unit}."]);
            }
        }

        DB::transaction(function () use ($validated, $product, $warehouseId, $qty, $type) {
            // Update warehouse_stocks atomically
            if ($type === 'Masuk') {
                WarehouseStock::updateOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $warehouseId],
                    ['quantity' => 0]
                );
                WarehouseStock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->increment('quantity', $qty);
            } else {
                WarehouseStock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->decrement('quantity', $qty);
            }

            // Record transaction
            InventoryTransaction::create([
                'product_id'   => $product->id,
                'warehouse_id' => $warehouseId,
                'supplier_id'  => $validated['supplier_id'] ?? null,
                'type'         => $type,
                'quantity'     => $qty,
                'operator_id'  => Auth::id(),
                'notes'        => $validated['notes'] ?? null,
                'status'       => 'completed',
            ]);
        });

        // Check stock alert after transaction (outside main transaction for safety)
        $this->alertService->checkAndAlert($product->id, $warehouseId);

        $verb = $type === 'Masuk' ? 'masuk' : 'keluar';

        return redirect()->route('transactions.index')
            ->with('success', "Transaksi barang {$verb} berhasil dicatat. {$qty} {$product->unit} {$product->name}.");
    }

    public function show(InventoryTransaction $transaction): View
    {
        $transaction->load(['product', 'warehouse', 'operator', 'supplier', 'sourceWarehouse', 'destinationWarehouse']);
        return view('transactions.show', compact('transaction'));
    }

    private function authorizeModify(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->canModify()) {
            abort(403);
        }
    }
}
