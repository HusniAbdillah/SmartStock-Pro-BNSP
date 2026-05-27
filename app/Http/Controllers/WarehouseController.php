<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::withSum('stocks', 'quantity')->orderBy('name')->paginate(20);
        return view('warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        $this->authorizeAdmin();
        return view('warehouses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'city'         => ['required', 'string', 'max:100'],
            'lat'          => ['required', 'numeric', 'between:-90,90'],
            'lng'          => ['required', 'numeric', 'between:-180,180'],
            'address'      => ['required', 'string'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'manager_name' => ['nullable', 'string', 'max:100'],
        ]);

        $warehouse = Warehouse::create($validated);

        return redirect()->route('warehouses.index')
            ->with('success', "Gudang «{$warehouse->name}» berhasil ditambahkan.");
    }

    public function show(Warehouse $warehouse): View
    {
        $warehouse->load('stocks.product.category');
        $stocks     = $warehouse->stocks;
        $totalStock = $stocks->sum('quantity');
        $totalValue = $stocks->sum(fn($s) => $s->quantity * ($s->product->price ?? 0));

        return view('warehouses.show', compact('warehouse', 'stocks', 'totalStock', 'totalValue'));
    }

    public function edit(Warehouse $warehouse): View
    {
        $this->authorizeAdmin();
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'city'         => ['required', 'string', 'max:100'],
            'lat'          => ['required', 'numeric', 'between:-90,90'],
            'lng'          => ['required', 'numeric', 'between:-180,180'],
            'address'      => ['required', 'string'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'manager_name' => ['nullable', 'string', 'max:100'],
        ]);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
            ->with('success', "Gudang «{$warehouse->name}» berhasil diperbarui.");
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($warehouse->stocks()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'Gudang tidak dapat dihapus karena masih memiliki stok barang.');
        }

        $warehouse->update(['is_active' => false]);

        return redirect()->route('warehouses.index')
            ->with('success', "Gudang «{$warehouse->name}» telah dinonaktifkan.");
    }

    private function authorizeAdmin(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403);
        }
    }
}
