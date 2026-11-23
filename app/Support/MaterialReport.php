<?php

namespace App\Support;

use App\Models\MaterialItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Menyusun data laporan material (2 tabel: Vendor Raw Material & Raw Material)
 * dari daftar MaterialItem + catatan kedatangannya.
 *
 * Dipakai bersama oleh: tampilan laporan di popup, halaman cetak PDF, dan export Excel.
 */
class MaterialReport
{
    /**
     * @param Collection<int, MaterialItem> $items  (relasi arrivals sudah di-eager-load)
     */
    public static function build(Collection $items): array
    {
        $vendor   = $items->where('category', MaterialItem::CATEGORY_VENDOR)->values();
        $material = $items->where('category', MaterialItem::CATEGORY_RAW)->values();

        return [
            'sections' => [
                self::section($vendor, [
                    'key'              => 'vendor',
                    'letter'           => 'A',
                    'title'            => 'Material Pengerjaan di Vendor (Vendor Raw Material)',
                    'incoming_title'   => 'INCOMING MATERIAL PENGERJAAN DI VENDOR',
                    'summary_label'    => 'Material Pengerjaan di Vendor',
                    'percentage_label' => 'PERCENTAGE MATERIAL PENGERJAAN DI VENDOR',
                ]),
                self::section($material, [
                    'key'              => 'material',
                    'letter'           => 'B',
                    'title'            => 'Material (Raw Material)',
                    'incoming_title'   => 'INCOMING MATERIAL',
                    'summary_label'    => 'Material',
                    'percentage_label' => 'PERCENTAGE MATERIAL',
                ]),
            ],
        ];
    }

    private static function section(Collection $items, array $meta): array
    {
        // Kolom tanggal HANYA dibuat untuk tanggal yang ada barang datang
        $dateKeys = $items
            ->flatMap(function (MaterialItem $item) {
                return $item->arrivals->map(function ($arrival) {
                    return $arrival->arrival_date->format('Y-m-d');
                });
            })
            ->unique()
            ->sort()
            ->values()
            ->all();

        $years       = array_unique(array_map(function ($key) {
            return substr($key, 0, 4);
        }, $dateKeys));
        $labelFormat = count($years) > 1 ? 'd-M-y' : 'd-M';

        $dates = [];
        foreach ($dateKeys as $key) {
            $carbon  = Carbon::parse($key);
            $dates[] = [
                'key'   => $key,
                'label' => $carbon->format($labelFormat),
                'full'  => $carbon->format('d M Y'),
            ];
        }

        $rows           = [];
        $sumQty         = 0.0;
        $sumTotalWeight = 0.0;
        $sumActual      = 0.0;
        $sumDeviation   = 0.0;
        $sumMill        = 0;
        $sumFulfilled   = 0.0; // jumlah yang sudah terpenuhi (dibatasi maksimal sebesar QTY diminta per item)

        foreach ($items->values() as $index => $item) {
            $byDate = [];
            foreach ($item->arrivals as $arrival) {
                $key          = $arrival->arrival_date->format('Y-m-d');
                $byDate[$key] = round(($byDate[$key] ?? 0) + $arrival->quantity, 2);
            }

            $actual = $item->actual;

            $rows[] = [
                'no'            => $index + 1,
                'id'            => $item->id,
                'name'          => $item->name,
                'spec'          => $item->spec,
                'purchase_date' => $item->purchase_date ? $item->purchase_date->format('d/m/Y') : null,
                'quantity'      => $item->quantity,
                'uom'           => $item->uom,
                'weight'        => $item->weight,
                'total_weight'  => $item->total_weight,
                'mill_cert'     => (bool) $item->mill_cert,
                'actual'        => $actual,
                'deviation'     => $item->deviation,
                'by_date'       => $byDate,
                'remarks'       => self::remarks($item),
            ];

            $sumMill        += $item->mill_cert ? 1 : 0;
            $sumQty         += $item->quantity;
            $sumTotalWeight += (float) $item->total_weight;
            $sumActual      += $actual;
            $sumDeviation   += $item->deviation;
            $sumFulfilled   += min($actual, $item->quantity);
        }

        $percentage = $sumQty > 0 ? min(100.0, $sumFulfilled / $sumQty * 100) : 0.0;

        return array_merge($meta, [
            'dates'  => $dates,
            'rows'   => $rows,
            'totals' => [
                'quantity'     => round($sumQty, 2),
                'total_weight' => round($sumTotalWeight, 2),
                'mill_cert'    => $sumMill,
                'actual'       => round($sumActual, 2),
                'deviation'    => round($sumDeviation, 2),
                'percentage'   => $percentage,
            ],
        ]);
    }

    // Kolom REMARK: gabungan remarks dari catatan kedatangan
    private static function remarks(MaterialItem $item): string
    {
        $entries = $item->arrivals->filter(function ($arrival) {
            return trim((string) $arrival->remarks) !== '';
        })->values();

        if ($entries->count() === 0) {
            return '';
        }

        if ($entries->count() === 1) {
            return trim($entries->first()->remarks);
        }

        return $entries->map(function ($arrival) {
            return $arrival->arrival_date->format('d-M') . ': ' . trim($arrival->remarks);
        })->implode('; ');
    }

    /**
     * Ringkasan singkat untuk badge di kotak Raw Material.
     */
    public static function summary(Collection $items): array
    {
        $count     = $items->count();
        $sumQty    = (float) $items->sum('quantity');
        $fulfilled = (float) $items->sum(function (MaterialItem $item) {
            return min($item->actual, $item->quantity);
        });

        $percentage = $sumQty > 0 ? min(100.0, $fulfilled / $sumQty * 100) : 0.0;

        return [
            'count'      => $count,
            'percentage' => $percentage,
            'progress'   => self::progressFromPercentage($percentage),
            'text'       => $count === 0
                ? 'Belum ada data'
                : $count . ' item · ' . self::percent($percentage) . ' datang',
        ];
    }

    /**
     * Persen barang datang (0-100, desimal) -> progress Raw Material (bilangan bulat 0-100).
     * 100 HANYA kalau semua item benar-benar sudah terpenuhi; selain itu dibulatkan, maksimal 99.
     */
    public static function progressFromPercentage(float $percentage): int
    {
        if ($percentage >= 99.995) {
            return 100;
        }

        return max(0, min(99, (int) round($percentage)));
    }

    // Format angka gaya Indonesia: 1.234,5 (tanpa desimal jika bilangan bulat)
    public static function number($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = round((float) $value, 2);
        if ($value == 0) {
            $value = 0.0; // hindari tampilan "-0"
        }

        $decimals = floor($value) == $value ? 0 : 2;
        $text     = number_format($value, $decimals, ',', '.');

        if ($decimals === 2) {
            $text = rtrim(rtrim($text, '0'), ',');
        }

        return $text;
    }

    public static function percent($value): string
    {
        return self::number(round((float) $value, 1)) . '%';
    }

    // Kelas CSS untuk sel DEVIASI
    public static function deviationClass($deviation): string
    {
        $deviation = round((float) $deviation, 2);

        if ($deviation > 0) {
            return 'dev-plus';
        }
        if ($deviation < 0) {
            return 'dev-minus';
        }

        return 'dev-zero';
    }
}