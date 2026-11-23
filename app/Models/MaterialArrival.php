<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialArrival extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_item_id',
        'arrival_date',
        'quantity',
        'remarks',
        'source',
        'created_by',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'quantity'     => 'float',
    ];

    public function item()
    {
        return $this->belongsTo(MaterialItem::class, 'material_item_id');
    }
}