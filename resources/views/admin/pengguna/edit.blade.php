@extends('layouts.app')
@section('title', 'Edit Pengguna')

@section('content')
@php $in = 'mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200'; @endphp
<div class="mx-auto max-w-xl px-4 py-6">
    <h1 class="text-2xl font-bold">Edit Akun {{ ucfirst($user->role) }}</h1>

    <form method="POST" action="{{ route('admin.pengguna.update', $user) }}" class="mt-5 space-y-4 rounded-2xl bg-white p-6 shadow">
        @csrf @method('PUT')
        <div>
            <label class="text-sm font-medium">Nama Petugas</label>
            <input name="nama_petugas" value="{{ old('nama_petugas', $user->nama_petugas) }}" class="{{ $in }}">
        </div>
        <div>
            <label class="text-sm font-medium">Username</label>
            <input name="username" value="{{ old('username', $user->username) }}" class="{{ $in }}">
        </div>
        <div>
            <label class="text-sm font-medium">Password baru <span class="text-slate-400">(kosongkan jika tidak diganti)</span></label>
            <input type="password" name="password" class="{{ $in }}">
        </div>
        <div class="flex gap-2 pt-2">
            <button class="rounded-lg bg-indigo-600 px-5 py-2 font-semibold text-white hover:bg-indigo-700">Simpan</button>
            <a href="{{ route('admin.pengguna.index') }}" class="rounded-lg bg-slate-200 px-5 py-2 hover:bg-slate-300">Batal</a>
        </div>
    </form>
</div>
@endsection