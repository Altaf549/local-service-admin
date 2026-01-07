<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicemanExperience extends Model
{
    protected $fillable = [
        'serviceman_id',
        'title',
        'description',
        'years',
        'company',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function serviceman(): BelongsTo
    {
        return $this->belongsTo(Serviceman::class);
    }
}
