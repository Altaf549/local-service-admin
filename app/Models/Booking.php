<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'booking_type',
        'service_id',
        'puja_id',
        'serviceman_id',
        'brahman_id',
        'booking_date',
        'booking_time',
        'address',
        'mobile_number',
        'notes',
        'status',
        'payment_status',
        'payment_method',
        'total_amount',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function puja(): BelongsTo
    {
        return $this->belongsTo(Puja::class);
    }

    public function serviceman(): BelongsTo
    {
        return $this->belongsTo(Serviceman::class);
    }

    public function brahman(): BelongsTo
    {
        return $this->belongsTo(Brahman::class);
    }
}
