@extends('layouts.app')
@section('title', 'Absen Wajah')

@push('styles')
<style>
    .scan-line {
        position: absolute; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, transparent, #22d3ee, transparent);
        box-shadow: 0 0 18px 6px rgba(34, 211, 238, .6);
        animation: scan 1.6s ease-in-out infinite;
    }
    @keyframes scan {
        0% { top: 0; }
        50% { top: calc(100% - 4px); }
        100% { top: 0; }
    }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-6xl px-4 py-6">
    <div class="grid gap-6 lg:grid-cols-2">

        <section class="rounded-2xl bg-white p-4 shadow">
            <div class="relative aspect-[4/3] overflow-hidden rounded-xl bg-slate-900">
                <video id="video" class="h-full w-full object-cover" autoplay muted playsinline></video>

                <div id="overlay-scan" class="pointer-events-none absolute inset-0 hidden">
                    <div class="scan-line"></div>
                    <div class="absolute inset-3 rounded-xl border-2 border-cyan-400/70"></div>
                </div>

                <div id="loading" class="absolute inset-0 flex items-center justify-center bg-slate-900/85 px-6 text-center text-sm text-white">
                    Memuat kamera dan model pengenalan wajah...
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <button id="btn-masuk" disabled
                    class="rounded-xl bg-indigo-600 py-3 text-lg font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                    Absen Masuk
                </button>
                <button id="btn-keluar" disabled
                    class="rounded-xl bg-slate-800 py-3 text-lg font-semibold text-white transition hover:bg-slate-900 disabled:cursor-not-allowed disabled:opacity-50">
                    Absen Keluar
                </button>
            </div>
            <p class="mt-2 text-center text-sm text-slate-500">Hadap kamera, pastikan wajah terang dan terlihat jelas.</p>
        </section>

        <section class="flex min-h-[380px] items-center justify-center rounded-2xl bg-white p-6 shadow">
            <div id="panel" class="w-full text-center"></div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/face-api.js') }}"></script>
<script src="{{ asset('js/face-common.js') }}"></script>
<script>
    const MODEL_URL = "{{ asset('models') }}";
    const VERIFY_URL = "{{ route('absen.verifikasi') }}";
    const HOME_URL = "{{ route('beranda') }}";
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const COUNTDOWN = 5;

    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay-scan');
    const loading = document.getElementById('loading');
    const btnMasuk = document.getElementById('btn-masuk');
    const btnKeluar = document.getElementById('btn-keluar');
    const panel = document.getElementById('panel');
    const warnaStatus = { Hadir: 'bg-emerald-100 text-emerald-700', Terlambat: 'bg-amber-100 text-amber-700' };
    let sibuk = false;

    const kunci = (nilai) => { btnMasuk.disabled = nilai; btnKeluar.disabled = nilai; };
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const tampil = (html) => { panel.innerHTML = html; };
    const hitungHtml = () => `<p class="mt-5 text-sm text-slate-500">Kembali ke beranda dalam <span id="hitung" class="font-bold text-slate-800">${COUNTDOWN}</span> detik</p>`;

    function mulaiHitungMundur() {
        let s = COUNTDOWN;
        const t = setInterval(() => {
            s--;
            const el = document.getElementById('hitung');
            if (el) el.textContent = s;
            if (s <= 0) { clearInterval(t); window.location.href = HOME_URL; }
        }, 1000);
    }

    function idle() {
        tampil(`<div class="text-slate-400"><div class="text-6xl"></div>
            <p class="mt-3 text-lg font-medium text-slate-600">Menunggu absen</p>
            <p class="text-sm">Hadapkan wajah ke kamera lalu pilih Absen Masuk atau Absen Keluar.</p></div>`);
    }

    function memindai() {
        tampil(`<div><div class="mx-auto h-14 w-14 animate-spin rounded-full border-4 border-cyan-400 border-t-transparent"></div>
            <p class="mt-4 text-lg font-semibold text-slate-700">Memindai wajah...</p>
            <p class="text-sm text-slate-500">Tetap menghadap kamera</p></div>`);
    }

    function pesanUlang(ikon, judul, isi) {
        tampil(`<div><div class="text-6xl">${ikon}</div>
            <p class="mt-3 text-xl font-bold text-slate-800">${esc(judul)}</p>
            <p class="mt-1 text-slate-600">${esc(isi)}</p>
            <div class="mt-6 flex justify-center gap-3">
                <button onclick="window.location.reload();" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                    Coba Lagi
                </button>
                <a href="${HOME_URL}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-300 transition">
                    Beranda
                </a>
            </div></div>`);
        sibuk = false; kunci(false);
    }

    function kartu(d, judul) {
        const s = d.siswa, p = d.presensi;
        const warna = warnaStatus[p.status] || 'bg-slate-100 text-slate-700';
        tampil(`
        <div class="mx-auto max-w-sm overflow-hidden rounded-2xl border border-slate-200 text-left shadow">
            <div class="bg-indigo-600 px-5 py-3 text-white">
                <p class="text-xs uppercase tracking-widest opacity-80">${esc(judul)}</p>
                <p class="text-sm">${esc(p.tanggal)}</p>
            </div>
            <div class="flex gap-4 p-5">
                <img src="${esc(s.foto || '')}" alt="" class="h-24 w-24 rounded-xl bg-slate-200 object-cover">
                <div class="min-w-0">
                    <p class="text-xl font-bold leading-tight text-slate-900">${esc(s.nama)}</p>
                    <p class="text-sm text-slate-500">NIS ${esc(s.nis)}</p>
                    <p class="text-sm text-slate-500">${esc(s.kelas)} · ${esc(s.jenis_kelamin)}</p>
                </div>
            </div>
            <div class="grid grid-cols-4 border-t text-center text-sm">
                <div class="p-2"><p class="text-slate-400">Status</p><span class="inline-block rounded-full px-2 py-0.5 font-semibold ${warna}">${esc(p.status)}</span></div>
                <div class="p-2"><p class="text-slate-400">Masuk</p><p class="font-semibold">${esc(p.waktu || '-')}</p></div>
                <div class="p-2"><p class="text-slate-400">Keluar</p><p class="font-semibold">${esc(p.waktu_keluar || '-')}</p></div>
                <div class="p-2"><p class="text-slate-400">Kecocokan</p><p class="font-semibold">${p.skor != null ? Math.round(p.skor * 100) + '%' : '-'}</p></div>
            </div>
        </div>${hitungHtml()}`);
        mulaiHitungMundur();
    }

    function belumTerdaftar() {
        tampil(`<div><div class="text-6xl">⚠️</div>
            <p class="mt-3 text-2xl font-bold text-red-600">Wajah belum didaftarkan</p>
            <p class="mt-1 text-slate-600">Segera hubungi guru untuk mendaftarkan wajah Anda.</p>
            <div class="mt-6 flex justify-center gap-3">
                <a href="${HOME_URL}" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                    Kembali ke Beranda
                </a>
            </div>
            ${hitungHtml()}</div>`);
        mulaiHitungMundur();
    }

    async function proses(jenis) {
        if (sibuk) return;
        sibuk = true; kunci(true);
        overlay.classList.remove('hidden');
        memindai();

        try {
            const sampel = await FaceKit.ambilSampel(video, 3, 7000);
            overlay.classList.add('hidden');

            if (sampel.length < 3) {
                return pesanUlang('📷', 'Wajah tidak terdeteksi', 'Dekatkan wajah ke kamera, pastikan cahaya cukup, lalu tekan tombol absen lagi.');
            }

            const res = await fetch(VERIFY_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    descriptors: sampel.map((s) => s.descriptor),
                    snapshot: FaceKit.snapshot(video, 320),
                    jenis: jenis,
                }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan pada server');

            if (data.status === 'ok') kartu(data, 'Absen Masuk Berhasil');
            else if (data.status === 'sudah') kartu(data, 'Anda sudah absen masuk hari ini');
            else if (data.status === 'ok_keluar') kartu(data, 'Absen Keluar Berhasil');
            else if (data.status === 'sudah_keluar') kartu(data, 'Anda sudah absen keluar hari ini');
            else if (data.status === 'belum_masuk') pesanUlang('⏰', 'Belum absen masuk', data.siswa.nama + ', Anda belum absen masuk hari ini. Silakan tekan Absen Masuk terlebih dahulu.');
            else if (data.status === 'unknown') belumTerdaftar();
            else pesanUlang('🔄', 'Belum yakin', 'Wajah belum terbaca jelas. Diamkan wajah menghadap kamera lalu coba lagi.');
        } catch (e) {
            overlay.classList.add('hidden');
            pesanUlang('❌', 'Terjadi kesalahan', e.message);
        }
    }

    btnMasuk.addEventListener('click', () => proses('masuk'));
    btnKeluar.addEventListener('click', () => proses('keluar'));

    (async () => {
        idle();
        try {
            await FaceKit.muatModel(MODEL_URL);
            await FaceKit.nyalakanKamera(video);
            loading.classList.add('hidden');
            kunci(false);
        } catch (e) {
            loading.textContent = 'Gagal memuat kamera/model: ' + e.message;
        }
    })();
</script>
@endpush
