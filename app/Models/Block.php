<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Block extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'blocks';

    protected $fillable = [
        'name',
        'condominium_id',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class, 'condominium_id', 'id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'block_id', 'id');
    }
}
