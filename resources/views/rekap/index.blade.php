@extends('layouts.app')
@section('title', 'Rekap Absensi')

@section('content')
@php
    $warna = ['Hadir' => 'bg-emerald-100 text-emerald-700', 'Terlambat' => 'bg-amber-100 text-amber-700', 'Izin' => 'bg-sky-100 text-sky-700',
              'Sakit' => 'bg-violet-100 text-violet-700', 'Alpa' => 'bg-red-100 text-red-700', 'Belum Hadir' => 'bg-slate-200 text-slate-600'];
    $isGuru = auth()->user()->role === 'guru';
    $batas = \Carbon\Carbon::parse($tanggal . ' ' . config('presensi.batas_masuk'));
@endphp
<div class="mx-auto max-w-6xl px-4 py-6">
    <h1 class="text-2xl font-bold">Rekapitulasi Absensi</h1>

    <p class="text-gray-600 font-medium mb-4">
        {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
    </p>

    <form method="GET" class="mt-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="text-sm font-medium">Tanggal</label>
            <input type="date"
                   name="tanggal"
                   value="{{ $tanggal }}"
                   onchange="this.form.submit()"
                   class="mt-1 block rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="text-sm font-medium">NIS / Nama</label>
            <input name="q"
                   value="{{ $q }}"
                   placeholder="Opsional"
                   class="mt-1 block rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <button class="rounded-lg bg-slate-800 px-4 py-2 text-white">Tampilkan</button>
    </form>

    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($ringkas as $label => $jumlah)
            <div class="rounded-2xl bg-white p-4 text-center shadow">
                <p class="text-2xl font-bold">{{ $jumlah }}</p>
                <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-semibold {{ $warna[$label] }}">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    <div class="mt-5 overflow-x-auto rounded-2xl bg-white shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="p-3">NIS</th><th class="p-3">Nama</th><th class="p-3">Status</th>
                    <th class="p-3">Jam Masuk</th><th class="p-3">Jam Keluar</th><th class="p-3">Keterangan</th>
                    @if ($isGuru)<th class="p-3">Izin / Sakit</th>@endif
                </tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($siswas as $s)
                @php $p = $s->presensis->first(); $st = $p?->status ?? 'Belum Hadir'; @endphp
                <tr>
                    <td class="p-3">{{ $s->nis }}</td>
                    <td class="p-3 font-medium">{{ $s->nama_siswa }}</td>
                    <td class="p-3"><span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $warna[$st] }}">{{ $st }}</span></td>
                    <td class="p-3">
                        {{ $p?->waktu_absen?->format('H:i:s') ?? '-' }}
                        @if ($st === 'Terlambat')
                            <span class="text-xs text-amber-600">(+{{ (int) $batas->diffInMinutes($p->waktu_absen) }} menit)</span>
                        @endif
                    </td>
                    <td class="p-3">{{ $p?->waktu_keluar?->format('H:i:s') ?? '-' }}</td>
                    <td class="p-3 text-slate-500">{{ $p?->keterangan ?? '-' }}</td>
                    @if ($isGuru)
                        <td class="p-3">
                            @if (! $p || in_array($st, ['Alpa', 'Izin', 'Sakit']))
                                <form method="POST" action="{{ route('rekap.status', $s) }}" class="flex flex-wrap items-center gap-1">
                                    @csrf
                                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                                    <select name="status" class="rounded border border-slate-300 px-1 py-1 text-xs">
                                        <option>Izin</option><option>Sakit</option>
                                    </select>
                                    <input name="keterangan" value="{{ $p?->keterangan }}" placeholder="Keterangan" class="w-32 rounded border border-slate-300 px-2 py-1 text-xs">
                                    <button class="rounded bg-indigo-600 px-2 py-1 text-xs text-white">Simpan</button>
                                </form>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="7" class="p-6 text-center text-slate-500">Data tidak ditemukan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @unless ($isGuru)
        <p class="mt-3 text-sm text-slate-500">Mode lihat saja. Laporkan siswa yang tidak masuk ke guru mapel.</p>
    @endunless
</div>
@endsection
