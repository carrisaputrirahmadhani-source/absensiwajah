@extends('layouts.app')
@section('title', 'Kelola Data Siswa')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Kelola Data Siswa</h1>
        <a href="{{ route('admin.siswa.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700">+ Tambah Siswa</a>
    </div>

    <form id="form-cari" class="mt-4 flex gap-2" method="GET">
        <input id="input-cari" name="q" value="{{ $q }}" placeholder="Cari NIS / nama..." class="w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2">
        <button class="rounded-lg bg-slate-800 px-4 py-2 text-white">Cari</button>
    </form>

    <div class="mt-4 overflow-x-auto rounded-2xl bg-white shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="p-3 w-16">No</th>
                    <th class="p-3">NIS</th>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Kelas</th>
                    <th class="p-3">JK</th>
                    <th class="p-3">Wajah</th>
                    <th class="p-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($siswas as $index => $s)
                <tr>
                    <td class="p-3 font-semibold text-slate-600">
                        {{ $siswas->firstItem() + $index }}
                    </td>
                    <td class="p-3">{{ $s->nis }}</td>
                    <td class="p-3 font-medium">{{ $s->nama_siswa }}</td>
                    <td class="p-3">{{ $s->kelas }}</td>
                    <td class="p-3">{{ $s->jenis_kelamin }}</td>
                    <td class="p-3">
                        @if ($s->terdaftar())
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Terverifikasi</span>
                        @else
                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-600">Belum Terdaftar</span>
                        @endif
                    </td>
                    <td class="p-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.siswa.edit', $s) }}" class="rounded bg-amber-100 px-3 py-1 text-amber-800 hover:bg-amber-200">Edit</a>
                            <form method="POST" action="{{ route('admin.siswa.destroy', $s) }}" onsubmit="return confirm('Hapus {{ $s->nama_siswa }}? Semua data presensinya ikut terhapus.')">
                                @csrf @method('DELETE')
                                <button class="rounded bg-red-100 px-3 py-1 text-red-700 hover:bg-red-200">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-6 text-center text-slate-500">Belum ada data siswa.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $siswas->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
    const formCari = document.getElementById('form-cari');
    const inputCari = document.getElementById('input-cari');

    // Saat kolom pencarian dikosongkan, langsung tampilkan semua siswa (tanpa menekan Enter)
    inputCari.addEventListener('input', () => {
        if (inputCari.value.trim() === '') {
            formCari.submit();
        }
    });
</script>
@endpush