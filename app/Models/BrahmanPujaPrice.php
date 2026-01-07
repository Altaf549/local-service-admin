<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrahmanPujaPrice extends Model
{
    protected $table = 'brahman_puja_prices';
    
    protected $fillable = [
        'brahman_id',
        'puja_id',
        'price',
        'material_file',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function brahman(): BelongsTo
    {
        return $this->belongsTo(Brahman::class);
    }

    public function puja(): BelongsTo
    {
        return $this->belongsTo(Puja::class);
    }
}
