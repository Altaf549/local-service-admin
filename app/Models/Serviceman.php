<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Serviceman extends Authenticatable
{
    use HasApiTokens;
    protected $fillable = [
        'name',
        'email',
        'mobile_number',
        'password',
        'phone',
        'service_category',
        'experience',
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
    ];

    protected $hidden = [
        'password',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_serviceman');
    }

    public function servicemanServicePrices(): HasMany
    {
        return $this->hasMany(ServicemanServicePrice::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(ServicemanExperience::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(ServicemanAchievement::class);
    }
}
