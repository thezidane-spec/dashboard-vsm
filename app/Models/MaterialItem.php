<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialItem extends Model
{
    use HasFactory;

    public const CATEGORY_RAW    = 'raw_material';
    public const CATEGORY_VENDOR = 'vendor_material';

    public static array $categoryLabels = [
        self::CATEGORY_RAW    => 'Raw Material',
        self::CATEGORY_VENDOR => 'Vendor Raw Material',
    ];

    protected $fillable = [
        'project_id',
        'radar_item_id',
        'radar_full_sync',
        'category',
        'name',
        'spec',
        'uom',
        'quantity',
        'weight',
        'purchase_date',
        'mill_cert',
        'created_by',
    ];

    protected $casts = [
        'quantity'      => 'float',
        'weight'        => 'float',
        'purchase_date' => 'date',
        'mill_cert'     => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Catatan kedatangan, urut dari tanggal paling awal
    public function arrivals()
    {
        return $this->hasMany(MaterialArrival::class, 'material_item_id')
            ->orderBy('arrival_date')
            ->orderBy('id');
    }

    // ACTUAL: total jumlah yang sudah datang
    public function getActualAttribute(): float
    {
        return round((float) $this->arrivals->sum('quantity'), 2);
    }

    // DEVIASI: ACTUAL - QTY diminta. 0 = sesuai, positif = lebih, negatif = kurang
    public function getDeviationAttribute(): float
    {
        $deviation = round($this->actual - (float) $this->quantity, 2);

        return $deviation == 0 ? 0.0 : $deviation; // hindari -0
    }

    // TOTAL berat = weight (per UOM) x QTY diminta
    public function getTotalWeightAttribute(): ?float
    {
        if ($this->weight === null) {
            return null;
        }

        return round($this->weight * (float) $this->quantity, 2);
    }
}