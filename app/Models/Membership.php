<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Membership extends Model
{
    use hasUuids, SoftDeletes;

    protected $table = 'memberships';

    protected $fillable = [
        'user_id',
        'condominium_id',
        'unit_id',
        'role',
        'is_active',
    ];
}
