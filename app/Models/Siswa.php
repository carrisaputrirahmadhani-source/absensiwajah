<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $table = 'siswas';

    protected $fillable = [
        'nis',
        'nama_siswa',
        'kelas',
        'jenis_kelamin',
        'foto',
        'face_descriptor',
        'akurasi_vektor',
        'guru_pendaftar_id',
        'waktu_daftar_wajah',
    ];

    protected function casts(): array
    {
        return [
            'face_descriptor'    => 'array',
            'akurasi_vektor'     => 'float',
            'waktu_daftar_wajah' => 'datetime',
        ];
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'siswa_id');
    }

    public function guruPendaftar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_pendaftar_id');
    }

    public function terdaftar(): bool
    {
        return ! empty($this->face_descriptor);
    }

    public function urlFoto(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }
}