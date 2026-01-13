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

    /**
     * Get full URL for profile photo.
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        if (!$this->profile_photo) {
            return asset('images/default-avatar.png');
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
            return '';
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

    public function experiences(): HasMany
    {
        return $this->hasMany(BrahmanExperience::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(BrahmanAchievement::class);
    }
}
