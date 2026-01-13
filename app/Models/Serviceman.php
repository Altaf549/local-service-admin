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

    /**
     * Get full URL for profile photo.
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        if (!$this->profile_photo) {
            return '';
        }

        // If it's already a full URL, return as is
        if (filter_var($this->profile_photo, FILTER_VALIDATE_URL)) {
            return $this->profile_photo;
        }

        // If it starts with /storage/, convert to full URL
        if (str_starts_with($this->profile_photo, '/storage/')) {
            return url($this->profile_photo);
        }

        // If it's a relative path, convert to full URL
        return asset('storage/' . $this->profile_photo);
    }

    /**
     * Get full URL for ID proof image.
     */
    public function getIdProofImageUrlAttribute(): string
    {
        if (!$this->id_proof_image) {
            return asset('images/default-document.png');
        }

        // If it's already a full URL, return as is
        if (filter_var($this->id_proof_image, FILTER_VALIDATE_URL)) {
            return $this->id_proof_image;
        }

        // If it starts with /storage/, convert to full URL
        if (str_starts_with($this->id_proof_image, '/storage/')) {
            return url($this->id_proof_image);
        }

        // If it's a relative path, convert to full URL
        return asset('storage/' . $this->id_proof_image);
    }

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
