<?php

if (! function_exists('tgl_indo')) {
    /**
     * Format Carbon/DateTime ke format tanggal Indonesia tanpa ekstensi intl.
     * Contoh: tgl_indo(now(), 'd F Y') → "28 Mei 2026"
     *         tgl_indo(now(), 'F Y')   → "Mei 2026"
     */
    function tgl_indo(\Carbon\Carbon $dt, string $format = 'd F Y'): string
    {
        $bulan = [
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
            4  => 'April',    5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',     8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',  11 => 'November',  12 => 'Desember',
        ];

        $hariPendek = [
            'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab',
            'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab', 'Sun' => 'Min',
        ];

        // Replace 'F' (full month) and 'M' (short month) placeholders
        $result = $dt->format($format);
        $result = str_replace($dt->format('F'), $bulan[(int) $dt->format('n')], $result);
        $result = str_replace($dt->format('M'), substr($bulan[(int) $dt->format('n')], 0, 3), $result);
        $result = str_replace($dt->format('D'), $hariPendek[$dt->format('D')] ?? $dt->format('D'), $result);

        return $result;
    }
}
