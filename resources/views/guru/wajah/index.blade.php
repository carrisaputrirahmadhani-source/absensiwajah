@extends('layouts.app')
@section('title', 'Pendaftaran Wajah')

@push('styles')
<style>
    .scan-line { position: absolute; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, transparent, #22d3ee, transparent);
        box-shadow: 0 0 18px 6px rgba(34, 211, 238, .6); animation: scan 1.6s ease-in-out infinite; }
    @keyframes scan { 0% { top: 0; } 50% { top: calc(100% - 4px); } 100% { top: 0; } }
    /* Tata letak dikunci: 4 kolom data, video 2/3 + pratinjau 1/3 */
    .wajah-data { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; }
    .wajah-media { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 1rem; margin-top: 1rem; }
    .wajah-cam { position: relative; aspect-ratio: 4 / 3; overflow: hidden; border-radius: .75rem; background: #0f172a; }
    .wajah-cam video { width: 100%; height: 100%; object-fit: cover; }
</style>
@endpush

@section('content')
@php $ro = 'mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600'; @endphp
<div class="mx-auto max-w-6xl px-4 py-6">
    <h1 class="text-2xl font-bold">Pendaftaran Wajah Siswa</h1>
    <p class="text-slate-500">Pilih siswa berstatus <b>Belum Terdaftar</b>, lalu rekam wajahnya.</p>

    <div class="mt-5 grid gap-6 lg:grid-cols-3">
        <aside class="rounded-2xl bg-white p-4 shadow">
            <h2 class="font-semibold">Belum Terdaftar ({{ $belum->count() }})</h2>
            <ul class="mt-3 max-h-80 space-y-1 overflow-y-auto">
                @forelse ($belum as $s)
                    <li>
                        <a href="{{ route('guru.wajah.index', ['siswa' => $s->id]) }}"
                           class="block rounded-lg px-3 py-2 text-sm hover:bg-indigo-50 {{ $dipilih?->id === $s->id ? 'bg-indigo-100 font-semibold text-indigo-700' : '' }}">
                            {{ $s->nama_siswa }} <span class="text-slate-400">· {{ $s->nis }}</span>
                        </a>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">Semua siswa sudah terdaftar 🎉</li>
                @endforelse
            </ul>

            <h2 class="mt-6 font-semibold">Terakhir didaftarkan hari ini</h2>
            <ul class="mt-2 space-y-1 text-sm text-slate-600">
                @forelse ($sudah as $s)
                    <li>✅ {{ $s->nama_siswa }} <span class="text-slate-400">{{ $s->waktu_daftar_wajah?->format('d/m H:i') }}</span></li>
                @empty
                    <li class="text-slate-400">Belum ada.</li>
                @endforelse
            </ul>
        </aside>

        <section class="rounded-2xl bg-white p-5 shadow lg:col-span-2">
            @if ($dipilih)
                <div class="wajah-data text-sm">
                    <div><label class="font-medium">NIS</label><input readonly value="{{ $dipilih->nis }}" class="{{ $ro }}"></div>
                    <div style="grid-column: span 2"><label class="font-medium">Nama</label><input readonly value="{{ $dipilih->nama_siswa }}" class="{{ $ro }}"></div>
                    <div><label class="font-medium">Kelas</label><input readonly value="{{ $dipilih->kelas }}" class="{{ $ro }}"></div>
                    <div style="grid-column: span 2"><label class="font-medium">Jenis Kelamin</label><input readonly value="{{ $dipilih->jenis_kelamin }}" class="{{ $ro }}"></div>
                </div>

                <div class="wajah-media">
                    <div class="wajah-cam">
                        <video id="video" autoplay muted playsinline></video>
                        <div id="overlay-scan" class="pointer-events-none absolute inset-0 hidden">
                            <div class="scan-line"></div>
                            <div class="absolute inset-3 rounded-xl border-2 border-cyan-400/70"></div>
                        </div>
                    </div>
                    <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 p-2 text-center text-sm text-slate-400">
                        <img id="preview" alt="" class="hidden w-full rounded-lg">
                        <span id="preview-kosong">Pratinjau hasil scan</span>
                    </div>
                </div>

                <p id="status" class="mt-3 text-sm text-slate-500">Memuat kamera dan model...</p>

                <div class="mt-3 flex flex-wrap gap-2">
                    <button id="btn-scan" type="button" disabled class="rounded-lg bg-slate-800 px-5 py-2 font-medium text-white disabled:opacity-50">Scan Wajah</button>

                    <form method="POST" action="{{ route('guru.wajah.store') }}">
                        @csrf
                        <input type="hidden" name="siswa_id" value="{{ $dipilih->id }}">
                        <input type="hidden" name="face_descriptor" id="in-desc">
                        <input type="hidden" name="akurasi_vektor" id="in-akurasi">
                        <input type="hidden" name="foto" id="in-foto">
                        <button id="btn-simpan" disabled class="rounded-lg bg-indigo-600 px-5 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Simpan Wajah Siswa</button>
                    </form>
                </div>
            @else
                <div class="grid h-64 place-items-center text-slate-400">Pilih siswa dari daftar di sebelah kiri.</div>
            @endif
        </section>
    </div>
</div>
@endsection

@if ($dipilih)
@push('scripts')
<script src="{{ asset('js/face-api.js') }}"></script>
<script src="{{ asset('js/face-common.js') }}"></script>
<script>
    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay-scan');
    const status = document.getElementById('status');
    const btnScan = document.getElementById('btn-scan');
    const btnSimpan = document.getElementById('btn-simpan');
    const preview = document.getElementById('preview');
    const previewKosong = document.getElementById('preview-kosong');

    const info = (t, err = false) => { status.textContent = t; status.className = 'mt-3 text-sm ' + (err ? 'text-red-600' : 'text-slate-500'); };
    const jarak = (a, b) => Math.sqrt(a.reduce((t, v, i) => t + (v - b[i]) ** 2, 0));

    (async () => {
        try {
            await FaceKit.muatModel("{{ asset('models') }}");
            await FaceKit.nyalakanKamera(video);
            btnScan.disabled = false;
            info('Kamera siap. Arahkan siswa menghadap kamera lalu tekan Scan Wajah.');
        } catch (e) { info('Gagal: ' + e.message, true); }
    })();

    btnScan.addEventListener('click', async () => {
        btnScan.disabled = true; btnSimpan.disabled = true;
        overlay.classList.remove('hidden');
        info('Memindai wajah... tahan posisi.');

        const sampel = await FaceKit.ambilSampel(video, 5, 10000);
        overlay.classList.add('hidden');
        btnScan.disabled = false;

        if (sampel.length < 5) return info('Wajah tidak terdeteksi / kualitas kurang. Perbaiki cahaya dan posisi, lalu scan ulang.', true);

        const ref = sampel[0].descriptor;
        if (sampel.some((s) => jarak(ref, s.descriptor) > 0.4)) return info('Hasil scan tidak konsisten. Diamkan wajah lalu scan ulang.', true);

        const rata = new Array(128).fill(0);
        sampel.forEach((s) => s.descriptor.forEach((v, i) => { rata[i] += v / sampel.length; }));

        const foto = FaceKit.snapshot(video, 320);
        document.getElementById('in-desc').value = JSON.stringify(rata);
        document.getElementById('in-akurasi').value = (sampel.reduce((t, s) => t + s.skor, 0) / sampel.length).toFixed(4);
        document.getElementById('in-foto').value = foto;

        preview.src = foto; preview.classList.remove('hidden'); previewKosong.classList.add('hidden');
        btnSimpan.disabled = false;
        info('Wajah berhasil dipindai. Periksa pratinjau, lalu tekan Simpan Wajah Siswa.');
    });
</script>
@endpush
@endif