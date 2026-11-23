<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Sebelumnya departemen PIC bernilai 'finishing' berlabel "QA/QC". Sekarang Finishing dan QA/QC dipisah:
// data lama dipindah ke 'qaqc' (label tetap QA/QC), 'finishing' kini berarti Finishing sebenarnya.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('projects')->whereNotNull('remarks_pics')->orderBy('id')->each(function ($row) {
            $pics = json_decode($row->remarks_pics, true);
            if (!is_array($pics)) {
                return;
            }
            $changed = false;
            foreach ($pics as &$pic) {
                if (is_array($pic) && ($pic['department'] ?? null) === 'finishing') {
                    $pic['department'] = 'qaqc';
                    $changed = true;
                }
            }
            unset($pic);
            if ($changed) {
                DB::table('projects')->where('id', $row->id)->update(['remarks_pics' => json_encode($pics, JSON_UNESCAPED_UNICODE)]);
            }
        });
    }

    public function down(): void
    {
        // tidak dibalik: label lama tidak bisa dibedakan dari data baru
    }
};