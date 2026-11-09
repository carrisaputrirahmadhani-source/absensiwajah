<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'q'       => ['nullable', 'string', 'max:100'],
        ]);

        $tanggal = $request->input('tanggal', today()->toDateString());
        $q = $request->input('q');

        $siswas = Siswa::with(['presensis' => fn ($x) => $x->where('tanggal', $tanggal)])
            ->when($q, fn ($x) => $x->where(
                fn ($y) => $y->where('nama_siswa', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%")
            ))
            ->orderBy('nama_siswa')->get();

        $ringkas = ['Hadir' => 0, 'Terlambat' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpa' => 0, 'Belum Hadir' => 0];
        foreach ($siswas as $s) {
            $ringkas[$s->presensis->first()?->status ?? 'Belum Hadir']++;
        }

        return view('rekap.index', compact('siswas', 'ringkas', 'tanggal', 'q'));
    }

    public function ubahStatus(Request $request, Siswa $siswa)
    {
        $data = $request->validate([
            'tanggal'    => ['required', 'date_format:Y-m-d'],
            'status'     => ['required', 'in:Izin,Sakit'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $ada = Presensi::where('siswa_id', $siswa->id)->where('tanggal', $data['tanggal'])->first();
        if ($ada && in_array($ada->status, ['Hadir', 'Terlambat'], true)) {
            return back()->withErrors(['status' => 'Siswa sudah absen lewat kamera, status tidak bisa diubah.']);
        }

        Presensi::updateOrCreate(
            ['siswa_id' => $siswa->id, 'tanggal' => $data['tanggal']],
            [
                'status'           => $data['status'],
                'keterangan'       => $data['keterangan'] ?? null,
                'waktu_absen'      => null,
                'waktu_keluar'     => null,
                'confidence_score' => null,
                'foto_snapshot'    => null,
                'perangkat'        => null,
            ]
        );

        return back()->with('sukses', "{$siswa->nama_siswa} dicatat {$data['status']}.");
    }
}
