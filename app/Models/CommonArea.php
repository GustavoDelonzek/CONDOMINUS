<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommonArea extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'common_areas';

    protected $fillable = [
        'condominium_id',
        'name',
        'capacity',
        'photo_url',
        'booking_rules',
        'is_active',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class, 'condominium_id', 'id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'common_area_id', 'id');
    }
}
