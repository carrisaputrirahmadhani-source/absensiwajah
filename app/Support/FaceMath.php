<?php

namespace App\Support;

class FaceMath
{
    /** Jarak Euclidean dua vektor 128-D. */
    public static function jarak(array $a, array $b): float
    {
        if (count($a) !== 128 || count($b) !== 128) {
            return INF;
        }

        $total = 0.0;
        for ($i = 0; $i < 128; $i++) {
            $d = (float) $a[$i] - (float) $b[$i];
            $total += $d * $d;
        }

        return sqrt($total);
    }
}