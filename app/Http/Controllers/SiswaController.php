<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        // Mengambil data siswa dan mengurutkannya berdasarkan NIS (nomor urut absen)
        $siswas = Siswa::when($q, fn ($x) => $x->where(
            fn ($y) => $y->where('nama_siswa', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%")
        ))->orderBy('nis', 'asc')->paginate(10)->withQueryString();

        return view('admin.siswa.index', compact('siswas', 'q'));
    }

    public function create()
    {
        return view('admin.siswa.form', ['siswa' => new Siswa(['kelas' => '12 RPL A'])]);
    }

    public function store(Request $request)
    {
        Siswa::create($this->validasi($request));

        return redirect()->route('admin.siswa.index')->with('sukses', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        return view('admin.siswa.form', compact('siswa'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $siswa->update($this->validasi($request, $siswa));

        return redirect()->route('admin.siswa.index')->with('sukses', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        if ($siswa->foto) {
            Storage::disk('public')->delete($siswa->foto);
        }
        $siswa->delete();

        return redirect()->route('admin.siswa.index')->with('sukses', 'Data siswa berhasil dihapus.');
    }

    private function validasi(Request $request, ?Siswa $siswa = null): array
    {
        return $request->validate([
            'nis'           => ['required', 'string', 'max:10', Rule::unique('siswas', 'nis')->ignore($siswa?->id)],
            'nama_siswa'    => ['required', 'string', 'max:100'],
            'kelas'         => ['required', 'string', 'max:20'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
        ]);
    }
}