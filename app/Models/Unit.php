<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'units';

    protected $fillable = [
        'condominium_id',
        'block_id',
        'number',
        'floor',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class, 'condominium_id', 'id');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class, 'block_id', 'id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'unit_id', 'id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'unit_id', 'id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'unit_id', 'id');
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(Occurrence::class, 'unit_id', 'id');
    }
}
