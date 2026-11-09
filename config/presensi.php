<?php

return [
    'batas_masuk'     => env('PRESENSI_BATAS_MASUK', '07:00'),
    'ambang_jarak'    => (float) env('PRESENSI_AMBANG_JARAK', 0.50), // makin kecil = makin ketat makin besar = makin longgar
    'margin_jarak'    => 0.05,  // selisih minimal kandidat terbaik vs kedua (anti tertukar)
    'ambang_duplikat' => 0.4,   // cegah 1 wajah didaftarkan ke 2 siswa
];