<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicemanServicePrice extends Model
{
    protected $table = 'serviceman_service_prices';
    
    protected $fillable = [
        'serviceman_id',
        'service_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function serviceman(): BelongsTo
    {
        return $this->belongsTo(Serviceman::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
