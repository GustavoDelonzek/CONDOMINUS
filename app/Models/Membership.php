<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class Membership extends Model
{
    use hasUuids, SoftDeletes;

    protected $table = 'memberships';

    protected $fillable = [
        'user_id',
        'admin_company_id',
        'condominium_id',
        'unit_id',
        'role',
        'is_active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class, 'condominium_id', 'id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}
