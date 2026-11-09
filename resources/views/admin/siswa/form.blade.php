@extends('layouts.app')
@section('title', $siswa->exists ? 'Edit Siswa' : 'Tambah Siswa')

@section('content')
@php $in = 'mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200'; @endphp
<div class="mx-auto max-w-xl px-4 py-6">
    <h1 class="text-2xl font-bold">{{ $siswa->exists ? 'Edit Data Siswa' : 'Tambah Siswa' }}</h1>

    <form method="POST" class="mt-5 space-y-4 rounded-2xl bg-white p-6 shadow"
          action="{{ $siswa->exists ? route('admin.siswa.update', $siswa) : route('admin.siswa.store') }}">
        @csrf
        @if ($siswa->exists) @method('PUT') @endif

        <div>
            <label class="text-sm font-medium">NIS</label>
            <input name="nis" maxlength="10" value="{{ old('nis', $siswa->nis) }}" class="{{ $in }}">
        </div>
        <div>
            <label class="text-sm font-medium">Nama Siswa</label>
            <input name="nama_siswa" value="{{ old('nama_siswa', $siswa->nama_siswa) }}" class="{{ $in }}">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Kelas</label>
                <input name="kelas" value="{{ old('kelas', $siswa->kelas) }}" class="{{ $in }}">
            </div>
            <div>
                <label class="text-sm font-medium">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="{{ $in }}">
                    @foreach (['Laki-laki', 'Perempuan'] as $jk)
                        <option value="{{ $jk }}" @selected(old('jenis_kelamin', $siswa->jenis_kelamin) === $jk)>{{ $jk }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex gap-2 pt-2">
            <button class="rounded-lg bg-indigo-600 px-5 py-2 font-semibold text-white hover:bg-indigo-700">Simpan</button>
            <a href="{{ route('admin.siswa.index') }}" class="rounded-lg bg-slate-200 px-5 py-2 hover:bg-slate-300">Batal</a>
        </div>
    </form>
</div>
@endsection