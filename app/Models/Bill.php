<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'bills';

    protected $fillable = [
        'condominium_id',
        'unit_id',
        'title',
        'amount',
        'due_date',
        'digitable_line',
        'pdf_url',
        'status'
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class, 'condominium_id', 'id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}
