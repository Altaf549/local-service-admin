<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PujaType extends Model
{
    protected $fillable = [
        'type_name',
        'image',
        'status',
    ];

    public function pujas(): HasMany
    {
        return $this->hasMany(Puja::class, 'puja_type_id');
    }
}
