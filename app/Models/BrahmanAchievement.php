<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrahmanAchievement extends Model
{
    protected $fillable = [
        'brahman_id',
        'title',
        'description',
        'achieved_date',
    ];

    protected $casts = [
        'achieved_date' => 'date',
    ];

    public function brahman(): BelongsTo
    {
        return $this->belongsTo(Brahman::class);
    }
}
