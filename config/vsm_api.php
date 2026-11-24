<?php

/**
 * REST API VSM (read-only) untuk website lain.
 * Isi di .env (boleh banyak token, pisahkan dengan koma):
 *   VSM_API_TOKENS=token-panjang-acak-1,token-panjang-acak-2
 * Jika kosong, API menolak semua permintaan (401).
 */
return [
    // true = tanpa token (bisa dibuka langsung di browser seperti /api/qc/summary). Aman hanya bila datanya memang boleh publik.
    'open'   => (bool) env('VSM_API_OPEN', false),
    'tokens' => array_values(array_filter(array_map('trim', explode(',', (string) env('VSM_API_TOKENS', ''))))),
];