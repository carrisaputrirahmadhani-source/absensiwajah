<?php

namespace App\Console\Commands;

use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Console\Command;

class TutupHariPresensi extends Command
{
    protected $signature = 'presensi:tutup-hari {--tanggal=}';

    protected $description = 'Tandai Alpa untuk siswa yang belum punya presensi pada hari ini';

    public function handle(): int
    {
        $tanggal = $this->option('tanggal') ?: today()->toDateString();
        $sudah   = Presensi::where('tanggal', $tanggal)->pluck('siswa_id');
        $dibuat  = 0;

        Siswa::whereNotIn('id', $sudah)->each(function (Siswa $s) use ($tanggal, &$dibuat) {
            Presensi::create([
                'siswa_id'   => $s->id,
                'tanggal'    => $tanggal,
                'status'     => 'Alpa',
                'keterangan' => 'Tidak hadir tanpa keterangan',
            ]);
            $dibuat++;
        });

        $this->info("Selesai: {$dibuat} siswa ditandai Alpa untuk {$tanggal}.");

        return self::SUCCESS;
    }
}