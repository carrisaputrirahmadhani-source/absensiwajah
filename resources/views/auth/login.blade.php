@extends('layouts.app')
@section('title', 'Login Petugas')

@section('content')
@php $in = 'mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200'; @endphp
<div class="mx-auto mt-12 max-w-md px-4">
    <div class="rounded-2xl bg-white p-8 shadow">
        <h1 class="text-2xl font-bold">Login Petugas</h1>
        <p class="text-sm text-slate-500">Khusus Admin, Guru, dan Sekretaris.</p>

        <form method="POST" action="{{ route('login.proses') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Hak Akses</label>
                <select name="role" class="{{ $in }}">
                    @foreach (['admin' => 'Admin Sistem', 'guru' => 'Guru Kelas', 'sekretaris' => 'Sekretaris'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('role') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Username</label>
                <input name="username" value="{{ old('username') }}" autofocus class="{{ $in }}">
            </div>
            <div>
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password" class="{{ $in }}">
            </div>
            <button class="w-full rounded-lg bg-indigo-600 py-2.5 font-semibold text-white hover:bg-indigo-700">Masuk</button>
        </form>
    </div>
</div>
@endsection