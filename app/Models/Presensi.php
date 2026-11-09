<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    protected $table = 'presensis';

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'waktu_absen',
        'waktu_keluar',
        'status',
        'keterangan',
        'confidence_score',
        'foto_snapshot',
        'perangkat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'          => 'date',
            'waktu_absen'      => 'datetime',
            'waktu_keluar'     => 'datetime',
            'confidence_score' => 'float',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
