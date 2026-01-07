<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Brahman extends Authenticatable
{
    use HasApiTokens;
    protected $table = 'brahmans';

    protected $fillable = [
        'name',
        'email',
        'mobile_number',
        'password',
        'specialization',
        'languages',
        'experience',
        'charges',
        'availability_status',
        'status',
        'government_id',
        'id_proof_image',
        'address',
        'profile_photo',
        'achievements',
    ];

    protected $casts = [
        'achievements' => 'array',
        'charges' => 'decimal:2',
    ];

    protected $hidden = [
        'password',
    ];

    public function experiences(): HasMany
    {
        return $this->hasMany(BrahmanExperience::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(BrahmanAchievement::class);
    }
}
