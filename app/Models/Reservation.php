<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'reservations';

    protected $fillable = [
        'condominium_id',
        'user_id',
        'unit_id',
        'common_area_id',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class, 'condominium_id', 'id');
    }

    public function commonArea(): BelongsTo
    {
        return $this->belongsTo(CommonArea::class, 'common_area_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}
