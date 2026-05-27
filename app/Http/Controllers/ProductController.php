<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBatchImport;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'supplier', 'warehouseStocks'])
            ->active();

        // Search — view form sends name="search"
        $search = $request->get('search', '');
        if ($search) {
            $query->search($search);
        }

        // Filter by category
        if ($categoryId = $request->get('category')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by stock_status — view sends stock_status=low|critical|normal
        $stockStatus = $request->get('stock_status');
        if ($stockStatus === 'low' || $stockStatus === 'critical') {
            $query->whereHas('warehouseStocks', function ($q) {
                $q->whereRaw('warehouse_stocks.quantity <= (SELECT minimum_threshold FROM products WHERE products.id = warehouse_stocks.product_id)');
            });
        }

        // Sort — view sends sort + direction
        $sort      = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $allowed   = ['name', 'sku', 'price', 'minimum_threshold', 'created_at'];
        if (in_array($sort, $allowed)) {
            $query->orderBy($sort, $direction);
        }

        $products   = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'suppliers', 'search', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorizeModify();

        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers', 'warehouses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeModify();

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'sku'               => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'category_id'       => ['required', 'exists:categories,id'],
            'supplier_id'       => ['nullable', 'exists:suppliers,id'],
            'description'       => ['nullable', 'string'],
            'price'             => ['required', 'numeric', 'min:0'],
            'minimum_threshold' => ['required', 'integer', 'min:1'],
            'unit'              => ['nullable', 'string', 'max:20'],
            'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'         => ['nullable', 'boolean'],
            'warehouse_id'      => ['nullable', 'exists:warehouses,id'],
            'initial_stock'     => ['nullable', 'integer', 'min:0'],
        ], $this->messages());

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Auto-generate SKU if not provided
        $sku = $validated['sku'] ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['name']), 0, 6)) . '-' . strtoupper(\Illuminate\Support\Str::random(4)); // auto-gen

        $product = Product::create([
            'name'              => $validated['name'],
            'sku'               => $sku,
            'category_id'       => $validated['category_id'],
            'supplier_id'       => $validated['supplier_id'] ?? null,
            'description'       => $validated['description'] ?? null,
            'price'             => $validated['price'],
            'minimum_threshold' => $validated['minimum_threshold'],
            'unit'              => $validated['unit'] ?? 'pcs',
            'image_path'        => $imagePath,
            'is_active'         => $request->boolean('is_active', true),
        ]);

        // Set initial stock for chosen warehouse
        $warehouseId  = $validated['warehouse_id'] ?? null;
        $initialStock = (int) ($validated['initial_stock'] ?? 0);
        if ($warehouseId && $initialStock > 0) {
            WarehouseStock::create([
                'product_id'   => $product->id,
                'warehouse_id' => $warehouseId,
                'quantity'     => $initialStock,
            ]);
            // Log as Masuk transaction
            \App\Models\InventoryTransaction::create([
                'product_id'   => $product->id,
                'warehouse_id' => $warehouseId,
                'type'         => 'Masuk',
                'quantity'     => $initialStock,
                'operator_id'  => Auth::id(),
                'notes'        => 'Stok awal produk',
                'status'       => 'completed',
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', "Produk «{$product->name}» berhasil ditambahkan.");
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'supplier', 'warehouseStocks.warehouse']);

        $transactions = $product->transactions()
            ->with(['warehouse', 'operator'])
            ->latest()
            ->take(20)
            ->get();

        return view('products.show', compact('product', 'transactions'));
    }

    public function edit(Product $product): View
    {
        $this->authorizeModify();

        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers', 'warehouses'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeModify();

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'sku'               => ['nullable', 'string', 'max:100', "unique:products,sku,{$product->id}"],
            'category_id'       => ['required', 'exists:categories,id'],
            'supplier_id'       => ['nullable', 'exists:suppliers,id'],
            'description'       => ['nullable', 'string'],
            'price'             => ['required', 'numeric', 'min:0'],
            'minimum_threshold' => ['required', 'integer', 'min:1'],
            'unit'              => ['nullable', 'string', 'max:20'],
            'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'         => ['nullable', 'boolean'],
        ], $this->messages());

        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name'              => $validated['name'],
            'sku'               => $validated['sku'] ?: $product->sku,
            'category_id'       => $validated['category_id'],
            'supplier_id'       => $validated['supplier_id'] ?? null,
            'description'       => $validated['description'] ?? null,
            'price'             => $validated['price'],
            'minimum_threshold' => $validated['minimum_threshold'],
            'unit'              => $validated['unit'] ?? $product->unit,
            'image_path'        => $imagePath,
            'is_active'         => $request->boolean('is_active', true),
        ]);

        return redirect()->route('products.show', $product)
            ->with('success', "Produk «{$product->name}» berhasil diperbarui.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        if ($product->transactions()->exists()) {
            return back()->with('error', 'Produk tidak dapat dihapus karena memiliki riwayat transaksi.');
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Produk «{$product->name}» berhasil dihapus.");
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorizeModify();

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], [
            'csv_file.required' => 'File CSV wajib diunggah.',
            'csv_file.mimes'    => 'Format file harus CSV.',
            'csv_file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $path = $request->file('csv_file')->store('imports', 'local');

        $job    = new ProcessBatchImport($path, Auth::id());
        $jobId  = dispatch($job);

        return redirect()->route('products.index')
            ->with('success', 'File CSV sedang diproses di latar belakang. Produk baru akan muncul segera.');
    }

    public function importStatus(string $jobId)
    {
        // Simplified status check
        return response()->json(['status' => 'processing', 'job_id' => $jobId]);
    }

    private function authorizeModify(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->canModify()) {
            abort(403, 'Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }
    }

    private function messages(): array
    {
        return [
            'name.required'              => 'Nama produk wajib diisi.',
            'sku.required'               => 'SKU produk wajib diisi.',
            'sku.unique'                 => 'SKU ini sudah digunakan produk lain.',
            'category_id.required'       => 'Kategori wajib dipilih.',
            'price.required'             => 'Harga produk wajib diisi.',
            'price.numeric'              => 'Harga harus berupa angka.',
            'minimum_threshold.required' => 'Batas minimum stok wajib diisi.',
            'image.image'                => 'File harus berupa gambar.',
            'image.max'                  => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
