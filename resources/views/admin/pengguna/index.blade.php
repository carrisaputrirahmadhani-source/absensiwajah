@extends('layouts.app')
@section('title', 'Kelola Pengguna')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">
    <h1 class="text-2xl font-bold">Kelola Akun Pengguna</h1>
    <p class="text-sm text-slate-500">Sistem memakai 3 akun tetap. Nama, username, dan password dapat diubah.</p>

    <div class="mt-4 overflow-x-auto rounded-2xl bg-white shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr><th class="p-3">Nama Petugas</th><th class="p-3">Username</th><th class="p-3">Role</th><th class="p-3"></th></tr>
            </thead>
            <tbody class="divide-y">
            @foreach ($users as $u)
                <tr>
                    <td class="p-3 font-medium">{{ $u->nama_petugas }}</td>
                    <td class="p-3">{{ $u->username }}</td>
                    <td class="p-3"><span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ ucfirst($u->role) }}</span></td>
                    <td class="p-3 text-right"><a href="{{ route('admin.pengguna.edit', $u) }}" class="rounded bg-amber-100 px-3 py-1 text-amber-800 hover:bg-amber-200">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection