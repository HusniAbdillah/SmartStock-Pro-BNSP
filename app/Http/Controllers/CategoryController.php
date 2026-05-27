<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->orderBy('name')->paginate(20);
        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorizeModify();
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeModify();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'color'       => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori sudah digunakan.',
            'color.regex'   => 'Format warna tidak valid (gunakan hex, contoh: #3b82f6).',
        ]);

        $category = Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', "Kategori «{$category->name}» berhasil ditambahkan.");
    }

    public function edit(Category $category): View
    {
        $this->authorizeModify();
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeModify();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', "unique:categories,name,{$category->id}"],
            'description' => ['nullable', 'string', 'max:255'],
            'color'       => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', "Kategori «{$category->name}» berhasil diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', "Kategori «{$category->name}» berhasil dihapus.");
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
