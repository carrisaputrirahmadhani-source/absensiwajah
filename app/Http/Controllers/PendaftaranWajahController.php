<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Support\FaceMath;
use App\Support\GambarBase64;
use Illuminate\Http\Request;

class PendaftaranWajahController extends Controller
{
    /**
     * Menampilkan halaman utama pendaftaran wajah siswa.
     */
    public function index(Request $request)
    {
        $belum = Siswa::whereNull('face_descriptor')->orderBy('nama_siswa')->get();
        $sudah = Siswa::whereNotNull('face_descriptor')->orderByDesc('waktu_daftar_wajah')->limit(8)->get();
        $dipilih = $request->filled('siswa') ? $belum->firstWhere('id', (int) $request->query('siswa')) : null;

        return view('guru.wajah.index', compact('belum', 'sudah', 'dipilih'));
    }

    /**
     * Memproses validasi, pengecekan duplikasi, hingga penyimpanan data wajah siswa baru ke database.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'siswa_id'        => ['required', 'exists:siswas,id'],
            'face_descriptor' => ['required', 'string'],
            'akurasi_vektor'  => ['required', 'numeric', 'between:0,1'],
            'foto'            => ['required', 'string'],
        ]);

        $siswa = Siswa::findOrFail($data['siswa_id']);

        if ($siswa->terdaftar()) {
            return back()->withErrors(['siswa_id' => 'Siswa ini sudah memiliki data wajah.']);
        }

        $vektor = json_decode($data['face_descriptor'], true);
        if (! is_array($vektor) || count($vektor) !== 128 || collect($vektor)->contains(fn ($v) => ! is_numeric($v))) {
            return back()->withErrors(['face_descriptor' => 'Data wajah tidak valid, silakan scan ulang.']);
        }
        $vektor = array_map('floatval', array_values($vektor));

        // Cegah wajah yang sama didaftarkan ke dua siswa
        foreach (Siswa::whereNotNull('face_descriptor')->get() as $lain) {
            if (FaceMath::jarak($vektor, $lain->face_descriptor) < config('presensi.ambang_duplikat')) {
                return back()->withErrors([
                    'face_descriptor' => "Wajah ini terlalu mirip dengan {$lain->nama_siswa} yang sudah terdaftar. Pastikan siswa yang dipilih benar.",
                ]);
            }
        }

        $path = GambarBase64::simpan($data['foto'], 'wajah');
        if (! $path) {
            return back()->withErrors(['foto' => 'Foto tidak valid, silakan scan ulang.']);
        }

        $siswa->update([
            'foto'               => $path,
            'face_descriptor'    => $vektor,
            'akurasi_vektor'     => $data['akurasi_vektor'],
            'guru_pendaftar_id'  => $request->user()->id,
            'waktu_daftar_wajah' => now(),
        ]);

        return redirect()->route('guru.wajah.index')
            ->with('sukses', "Wajah {$siswa->nama_siswa} berhasil didaftarkan.");
    }
}