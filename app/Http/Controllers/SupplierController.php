<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::withCount('products')
            ->when(request('q'), fn($q, $search) => $q->where('name', 'LIKE', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        $this->authorizeModify();
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeModify();

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:200'],
            'contact_person' => ['required', 'string', 'max:100'],
            'phone'          => ['required', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:100'],
            'address'        => ['required', 'string'],
            'city'           => ['nullable', 'string', 'max:100'],
        ]);

        $supplier = Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier «{$supplier->name}» berhasil ditambahkan.");
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['products', 'transactions' => fn($q) => $q->latest()->take(10)->with(['product', 'warehouse'])]);
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorizeModify();
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->authorizeModify();

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:200'],
            'contact_person' => ['required', 'string', 'max:100'],
            'phone'          => ['required', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:100'],
            'address'        => ['required', 'string'],
            'city'           => ['nullable', 'string', 'max:100'],
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier «{$supplier->name}» berhasil diperbarui.");
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        $supplier->update(['is_active' => false]);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier «{$supplier->name}» telah dinonaktifkan.");
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
