<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Occurrence extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'occurrences';

    protected $fillable = [
        'condominium_id',
        'user_id',
        'unit_id',
        'category',
        'description',
        'status',
        'admin_response',
        'responded_at'
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class, 'condominium_id', 'id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function occurrenceMedias(): HasMany
    {
        return $this->hasMany(OccurrenceMedia::class, 'occurrence_id', 'id');
    }
}
