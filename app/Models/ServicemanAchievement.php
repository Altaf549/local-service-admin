<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicemanAchievement extends Model
{
    protected $fillable = [
        'serviceman_id',
        'title',
        'description',
        'achieved_date',
    ];

    protected $casts = [
        'achieved_date' => 'date',
    ];

    public function serviceman(): BelongsTo
    {
        return $this->belongsTo(Serviceman::class);
    }
}
