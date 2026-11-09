<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function index()
    {
        return view('admin.pengguna.index', ['users' => User::orderBy('role')->get()]);
    }

    public function edit(User $pengguna)
    {
        return view('admin.pengguna.edit', ['user' => $pengguna]);
    }

    public function update(Request $request, User $pengguna)
    {
        $data = $request->validate([
            'nama_petugas' => ['required', 'string', 'max:100'],
            'username'     => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($pengguna->id)],
            'password'     => ['nullable', 'string', 'min:6'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $pengguna->update($data);

        return redirect()->route('admin.pengguna.index')->with('sukses', 'Akun berhasil diperbarui.');
    }
}