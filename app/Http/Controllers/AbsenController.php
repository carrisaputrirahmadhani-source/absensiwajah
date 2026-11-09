<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Siswa;
use App\Support\FaceMath;
use App\Support\GambarBase64;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AbsenController extends Controller
{
    public function beranda()
    {
        $total = Siswa::count();
        $hadir = Presensi::where('tanggal', today()->toDateString())
            ->whereIn('status', ['Hadir', 'Terlambat'])->count();

        return view('beranda.index', compact('total', 'hadir'));
    }

    public function kamera()
    {
        return view('beranda.absen');
    }

    public function verifikasi(Request $request): JsonResponse
    {
        $request->validate([
            'descriptors'     => ['required', 'array', 'min:3', 'max:5'],
            'descriptors.*'   => ['array', 'size:128'],
            'descriptors.*.*' => ['numeric'],
            'snapshot'        => ['nullable', 'string'],
            'jenis'           => ['nullable', 'in:masuk,keluar'],
        ]);

        $jenis = $request->input('jenis', 'masuk');

        $kandidat = Siswa::whereNotNull('face_descriptor')->get();
        $ambang   = (float) config('presensi.ambang_jarak');
        $margin   = (float) config('presensi.margin_jarak');

        if ($kandidat->isEmpty()) {
            return response()->json(['status' => 'unknown']);
        }

        $hasil = collect($request->input('descriptors'))->map(function ($vektor) use ($kandidat) {
            $terbaik = null;
            $dBest   = INF;
            $dKedua  = INF;

            foreach ($kandidat as $s) {
                $d = FaceMath::jarak($vektor, $s->face_descriptor);
                if ($d < $dBest) {
                    $dKedua = $dBest;
                    $dBest  = $d;
                    $terbaik = $s;
                } elseif ($d < $dKedua) {
                    $dKedua = $d;
                }
            }

            return ['siswa' => $terbaik, 'd' => $dBest, 'kedua' => $dKedua];
        });

        if ($hasil->every(fn ($h) => $h['d'] >= $ambang)) {
            return response()->json(['status' => 'unknown']);
        }

        $idUnik = $hasil->map(fn ($h) => $h['siswa']?->id)->unique();
        $yakin  = $hasil->every(fn ($h) => $h['d'] < $ambang && ($h['kedua'] - $h['d']) >= $margin);

        if ($idUnik->count() !== 1 || ! $yakin) {
            return response()->json(['status' => 'ulang']);
        }

        $siswa   = $hasil->first()['siswa'];
        $skor    = round(1 - $hasil->avg('d'), 4);
        $hariIni = today()->toDateString();

        $sudah = Presensi::where('siswa_id', $siswa->id)->where('tanggal', $hariIni)->first();

        if ($jenis === 'keluar') {
            return $this->absenKeluar($siswa, $sudah);
        }

        if ($sudah) {
            return response()->json(['status' => 'sudah'] + $this->data($siswa, $sudah));
        }

        $sekarang = now();
        $batas    = now()->setTimeFromTimeString(config('presensi.batas_masuk'));
        $status   = $sekarang->lte($batas) ? 'Hadir' : 'Terlambat';

        try {
            $presensi = Presensi::create([
                'siswa_id'         => $siswa->id,
                'tanggal'          => $hariIni,
                'waktu_absen'      => $sekarang,
                'status'           => $status,
                'confidence_score' => $skor,
                'foto_snapshot'    => GambarBase64::simpan($request->input('snapshot'), 'snapshots'),
                'perangkat'        => Str::limit((string) $request->userAgent(), 50, ''),
            ]);
        } catch (QueryException $e) {
            $presensi = Presensi::where('siswa_id', $siswa->id)->where('tanggal', $hariIni)->first();
            if (! $presensi) {
                throw $e;
            }
            return response()->json(['status' => 'sudah'] + $this->data($siswa, $presensi));
        }

        return response()->json(['status' => 'ok'] + $this->data($siswa, $presensi));
    }

    private function absenKeluar(Siswa $siswa, ?Presensi $presensi): JsonResponse
    {
        if (! $presensi || ! in_array($presensi->status, ['Hadir', 'Terlambat'], true)) {
            return response()->json(['status' => 'belum_masuk'] + $this->data($siswa, $presensi));
        }

        if ($presensi->waktu_keluar) {
            return response()->json(['status' => 'sudah_keluar'] + $this->data($siswa, $presensi));
        }

        $diubah = Presensi::where('id', $presensi->id)
            ->whereNull('waktu_keluar')
            ->update(['waktu_keluar' => now()]);

        $presensi->refresh();

        if (! $diubah) {
            return response()->json(['status' => 'sudah_keluar'] + $this->data($siswa, $presensi));
        }

        return response()->json(['status' => 'ok_keluar'] + $this->data($siswa, $presensi));
    }

    private function data(Siswa $s, ?Presensi $p): array
    {
        $snapshot = $p?->foto_snapshot ? asset('storage/' . $p->foto_snapshot) : null;

        return [
            'siswa' => [
                'nama'          => $s->nama_siswa,
                'nis'           => $s->nis,
                'kelas'         => $s->kelas,
                'jenis_kelamin' => $s->jenis_kelamin,
                'foto'          => $s->urlFoto() ?? $snapshot,
            ],
            'presensi' => $p ? [
                'status'       => $p->status,
                'waktu'        => $p->waktu_absen?->format('H:i:s'),
                'waktu_keluar' => $p->waktu_keluar?->format('H:i:s'),
                'tanggal'      => $p->tanggal->translatedFormat('l, d F Y'),
                'skor'         => $p->confidence_score,
            ] : null,
        ];
    }
}
