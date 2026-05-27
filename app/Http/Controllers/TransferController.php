<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\StockAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function __construct(private readonly StockAlertService $alertService) {}

    public function index(): View
    {
        $transfers = InventoryTransaction::with(['product', 'sourceWarehouse', 'destinationWarehouse', 'operator'])
            ->ofType('Transfer')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('transfers.index', compact('transfers'));
    }

    public function create(): View
    {
        $this->authorizeModify();

        $products   = Product::active()->with('category')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('transfers.create', compact('products', 'warehouses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeModify();

        $validated = $request->validate([
            'product_id'            => ['required', 'exists:products,id'],
            'source_warehouse_id'   => ['required', 'exists:warehouses,id'],
            'destination_warehouse_id' => ['required', 'exists:warehouses,id', 'different:source_warehouse_id'],
            'quantity'              => ['required', 'integer', 'min:1'],
            'notes'                 => ['nullable', 'string', 'max:500'],
        ], [
            'product_id.required'                  => 'Produk wajib dipilih.',
            'source_warehouse_id.required'         => 'Gudang asal wajib dipilih.',
            'destination_warehouse_id.required'    => 'Gudang tujuan wajib dipilih.',
            'destination_warehouse_id.different'   => 'Gudang tujuan tidak boleh sama dengan gudang asal.',
            'quantity.required'                    => 'Jumlah wajib diisi.',
            'quantity.min'                         => 'Jumlah minimal 1.',
        ]);

        $product      = Product::findOrFail($validated['product_id']);
        $srcId        = $validated['source_warehouse_id'];
        $dstId        = $validated['destination_warehouse_id'];
        $qty          = $validated['quantity'];

        // Validate source stock
        $sourceStock = $product->stockAtWarehouse($srcId);
        if ($sourceStock < $qty) {
            return back()->withInput()->withErrors([
                'quantity' => "Stok di gudang asal tidak mencukupi. Tersedia: {$sourceStock} {$product->unit}.",
            ]);
        }

        // Both warehouse stock rows are mutated atomically.
        // We lock the source row first (lockForUpdate) to prevent concurrent
        // over-deduction races before decrementing.
        DB::transaction(function () use ($product, $srcId, $dstId, $qty, $validated) {
            // Re-read and lock source row inside the transaction to prevent concurrent over-deduction
            $sourceRow = WarehouseStock::where('product_id', $product->id)
                ->where('warehouse_id', $srcId)
                ->lockForUpdate()
                ->first();

            // Re-validate stock inside the transaction with the locked row
            if (!$sourceRow || $sourceRow->quantity < $qty) {
                throw new \Illuminate\Validation\ValidationException(
                    \Illuminate\Support\Facades\Validator::make([], []),
                    back()->withInput()->withErrors([
                        'quantity' => 'Stok di gudang asal tidak mencukupi (concurrent check gagal).',
                    ])
                );
            }

            $sourceRow->decrement('quantity', $qty);

            // Ensure destination row exists, then increment atomically
            WarehouseStock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $dstId],
                ['quantity'   => 0]
            );
            WarehouseStock::where('product_id', $product->id)
                ->where('warehouse_id', $dstId)
                ->increment('quantity', $qty);

            // Record a single Transfer transaction
            InventoryTransaction::create([
                'product_id'               => $product->id,
                'warehouse_id'             => $srcId,
                'type'                     => 'Transfer',
                'quantity'                 => $qty,
                'source_warehouse_id'      => $srcId,
                'destination_warehouse_id' => $dstId,
                'operator_id'              => Auth::id(),
                'notes'                    => $validated['notes'] ?? null,
                'status'                   => 'completed',
            ]);
        });

        // Post-transaction: check if source warehouse is now critically low
        $this->alertService->checkAndAlert($product->id, $srcId);

        $srcWarehouse = Warehouse::find($srcId);
        $dstWarehouse = Warehouse::find($dstId);

        return redirect()->route('transfers.index')
            ->with('success', "Transfer berhasil: {$qty} {$product->unit} «{$product->name}» dari {$srcWarehouse->city} ke {$dstWarehouse->city}.");
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
