<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrahmanExperience extends Model
{
    protected $fillable = [
        'brahman_id',
        'title',
        'description',
        'years',
        'organization',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function brahman(): BelongsTo
    {
        return $this->belongsTo(Brahman::class);
    }
}
