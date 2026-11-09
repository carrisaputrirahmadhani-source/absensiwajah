<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['username' => 'admin',      'password' => 'admin123',      'nama_petugas' => 'Admin Sistem', 'role' => 'admin']);
        User::create(['username' => 'guru',       'password' => 'guru123',       'nama_petugas' => 'Guru Kelas',   'role' => 'guru']);
        User::create(['username' => 'sekretaris', 'password' => 'sekretaris123', 'nama_petugas' => 'Sekretaris',   'role' => 'sekretaris']);

        // Data contoh, hapus/ganti dengan siswa asli lewat login Admin
        foreach ([
            ['1001', 'Ahmad Fauzi', 'Laki-laki'],
            ['1002', 'Siti Aisyah', 'Perempuan'],
            ['1003', 'Budi Santoso', 'Laki-laki'],
        ] as [$nis, $nama, $jk]) {
            Siswa::create(['nis' => $nis, 'nama_siswa' => $nama, 'kelas' => '12 RPL A', 'jenis_kelamin' => $jk]);
        }
    }
}