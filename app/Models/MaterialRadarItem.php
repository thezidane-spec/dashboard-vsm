<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRadarItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'bom_date' => 'date', 'pr_date' => 'date', 'po_date' => 'date',
        'required_date' => 'date', 'arrival_date' => 'date',
    ];

    // PIC yang berhak mengubah tiap kolom (warna kolom mengikuti PIC-nya)
    public static array $fieldPic = [
        'no' => 'engineering', 'material_code' => 'engineering', 'description' => 'engineering', 'spec' => 'engineering', 'req_qty' => 'engineering',
        'bom_no' => 'engineering', 'bom_date' => 'engineering',
        'avail_qty' => 'warehouse', 'arrival_date' => 'warehouse', 'arrival_qty' => 'warehouse',
        'pr_no' => 'ppic', 'pr_qty' => 'ppic', 'pr_date' => 'ppic', 'required_date' => 'ppic', 'remarks' => 'ppic',
        'po_no' => 'procurement', 'po_qty' => 'procurement', 'po_date' => 'procurement',
    ];

    public static array $dateFields   = ['bom_date', 'pr_date', 'po_date', 'required_date', 'arrival_date'];
    public static array $numberFields = ['req_qty', 'avail_qty', 'pr_qty', 'po_qty', 'arrival_qty'];

    /** Role aplikasi -> PIC yang boleh diedit. Admin boleh semua. Warehouse dipegang role production. */
    public static function picsForRole(?string $role): array
    {
        return match (strtolower((string) $role)) {
            'admin'       => ['engineering', 'ppic', 'warehouse', 'procurement'],
            'engineering' => ['engineering'],
            'ppic'        => ['ppic'],
            'production'  => ['warehouse'],
            'procurement' => ['procurement'],
            default       => [],
        };
    }

    public static function parseNumber($value): ?float
    {
        if ($value === null) { return null; }
        $s = trim((string) $value);
        if ($s === '') { return null; }
        if (preg_match('/-?\d[\d.,]*/', $s, $m)) {
            $n = $m[0];
            // "1.200,5" / "1,200.5" / "1200,5"
            if (str_contains($n, ',') && str_contains($n, '.')) {
                $n = strrpos($n, ',') > strrpos($n, '.') ? str_replace(['.', ','], ['', '.'], $n) : str_replace(',', '', $n);
            } elseif (str_contains($n, ',')) {
                $n = preg_match('/,\d{3}$/', $n) ? str_replace(',', '', $n) : str_replace(',', '.', $n);
            }
            return (float) $n;
        }
        return null;
    }

    public static function parseDate($value): ?string
    {
        $s = trim((string) $value);
        if ($s === '') { return null; }

        // angka serial Excel / Google Sheets yang tidak berformat tanggal (mis. 46325 atau 46325.5)
        if (preg_match('/^\d{5}(\.\d+)?$/', $s) && (float) $s > 20000 && (float) $s < 80000) {
            return (new \DateTime('1899-12-30'))->modify('+' . (int) floor((float) $s) . ' days')->format('Y-m-d');
        }

        // nama hari & bulan bahasa Indonesia -> Inggris ("Jum, 30 Okt 2026", "30-Agu-26", "Mei", "Des")
        $s = preg_replace('/^(senin|selasa|rabu|kamis|jumat|jum\'at|sabtu|minggu|sen|sel|rab|kam|jum|sab|min|mon|tue|wed|thu|fri|sat|sun)[a-z]*[,.\s]+/iu', '', $s);
        $map = ['januari' => 'jan', 'februari' => 'feb', 'maret' => 'mar', 'april' => 'apr', 'mei' => 'may', 'juni' => 'jun', 'juli' => 'jul',
                'agustus' => 'aug', 'agu' => 'aug', 'agt' => 'aug', 'september' => 'sep', 'sept' => 'sep', 'oktober' => 'oct', 'okt' => 'oct',
                'november' => 'nov', 'nop' => 'nov', 'desember' => 'dec', 'des' => 'dec'];
        $s = preg_replace_callback('/[a-z]+/iu', fn($m) => $map[mb_strtolower($m[0])] ?? $m[0], $s);
        $s = trim(preg_replace('/\s+/', ' ', str_replace(["\u{a0}", ','], [' ', ' '], $s)));

        // 30-Oct-26 / 30 Oct 26 (tahun 2 digit)
        if (preg_match('/^(\d{1,2})[\s\/.-]+([a-z]{3,9})[\s\/.-]+(\d{2})$/i', $s, $m)) {
            $s = $m[1] . ' ' . $m[2] . ' 20' . $m[3];
        }
        // 30/10/26 (tahun 2 digit)
        if (preg_match('/^(\d{1,2})[\/.-](\d{1,2})[\/.-](\d{2})$/', $s, $m)) {
            $s = sprintf('%02d/%02d/20%s', $m[1], $m[2], $m[3]);
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $s, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }
        if (preg_match('/^(\d{1,2})[\/.-](\d{1,2})[\/.-](\d{4})$/', $s, $m) && checkdate((int) $m[2], (int) $m[1], (int) $m[3])) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        try {
            return \Carbon\Carbon::parse($s)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}