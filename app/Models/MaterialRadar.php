<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Satu sheet "Material Radar List" = satu proyek. */
class MaterialRadar extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(MaterialRadarItem::class)->orderBy('sort')->orderBy('id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getLabelAttribute(): string
    {
        return trim(($this->project_no ? $this->project_no . ' - ' : '') . ($this->project_name ?: $this->sheet_name ?: 'Tanpa nama'));
    }
}