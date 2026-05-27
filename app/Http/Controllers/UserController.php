<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:150'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'role'                  => ['required', 'in:Admin,Manajer Gudang,Staf Gudang,Viewer'],
            'password'              => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'name.required'         => 'Nama wajib diisi.',
            'email.unique'          => 'Email sudah terdaftar.',
            'password.min'          => 'Kata sandi minimal 8 karakter.',
            'password.letters'      => 'Kata sandi harus mengandung huruf.',
            'password.numbers'      => 'Kata sandi harus mengandung angka.',
            'password.confirmed'    => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')
            ->with('success', "Pengguna «{$user->name}» berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', "unique:users,email,{$user->id}"],
            'role'      => ['required', 'in:Admin,Manajer Gudang,Staf Gudang,Viewer'],
            'is_active' => ['boolean'],
            'password'  => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $updateData = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', "Data pengguna «{$user->name}» berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->update(['is_active' => false]);

        return redirect()->route('users.index')
            ->with('success', "Pengguna «{$user->name}» telah dinonaktifkan.");
    }
}
