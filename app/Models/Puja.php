<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Puja extends Model
{
    protected $fillable = [
        'puja_name',
        'puja_type_id',
        'duration',
        'price',
        'description',
        'image',
        'material_file',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function pujaType(): BelongsTo
    {
        return $this->belongsTo(PujaType::class, 'puja_type_id');
    }
}
