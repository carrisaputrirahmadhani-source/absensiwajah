@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-14 text-center">
    <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Kelas 12 RPL A</p>
    <h1 class="mt-3 text-4xl font-extrabold text-slate-900 md:text-5xl">Sistem Presensi Wajah</h1>
    <p class="mt-4 text-slate-600">Absen cukup dengan menghadap kamera. Tanpa login, tanpa kartu.</p>

    <div id="jam" class="mt-8 text-5xl font-bold tabular-nums text-slate-800"></div>
    <div id="tanggal" class="mt-1 text-slate-500"></div>

    <a href="{{ route('absen') }}"
       class="mt-8 inline-block rounded-2xl bg-indigo-600 px-10 py-4 text-lg font-semibold text-white shadow-lg transition hover:bg-indigo-700">
        Mulai Absen Wajah
    </a>

    <div class="mt-12 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 shadow"><p class="text-3xl font-bold">{{ $total }}</p><p class="text-sm text-slate-500">Total siswa</p></div>
        <div class="rounded-2xl bg-white p-5 shadow"><p class="text-3xl font-bold text-emerald-600">{{ $hadir }}</p><p class="text-sm text-slate-500">Sudah absen hari ini</p></div>
        <div class="rounded-2xl bg-white p-5 shadow"><p class="text-3xl font-bold text-amber-600">{{ max($total - $hadir, 0) }}</p><p class="text-sm text-slate-500">Belum absen</p></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function tick() {
        const d = new Date();
        document.getElementById('jam').textContent = d.toLocaleTimeString('id-ID').replace(/\./g, ':');
        document.getElementById('tanggal').textContent = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }
    tick(); setInterval(tick, 1000);
</script>
@endpush